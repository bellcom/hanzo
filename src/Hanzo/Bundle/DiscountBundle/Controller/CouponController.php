<?php

namespace Hanzo\Bundle\DiscountBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormError;

use Criteria;

use Hanzo\Core\Hanzo;
use Hanzo\Core\Tools;
use Hanzo\Core\CoreController;

use Hanzo\Model\Coupons;
use Hanzo\Model\CouponsQuery;
use Hanzo\Model\OrdersPeer;
use Hanzo\Model\OrdersToCoupons;

class CouponController extends CoreController
{
    public function blockAction()
    {
        return $this->render('DiscountBundle:Coupon:block.html.twig');
    }

    public function applyAction(Request $request)
    {
        $translator = $this->get('translator');

        $form = $this->createFormBuilder(new Coupons())
            ->add('code', 'text', [
                'label'              => 'coupon.label',
                'error_bubbling'     => true,
                'translation_domain' => 'checkout',
            ])
            ->getForm()
        ;

        if ($request->getMethod() == 'POST') {
            $values = $request->get('form');
            $code   = $values ?: $request->get('code');
            $order  = OrdersPeer::getCurrent();
            $total  = $order->getTotalPrice();

            $now = time();
            $coupon = CouponsQuery::create()
                ->filterByCode($code)
                ->filterByIsUsed(0)
                ->filterByIsActive(1)
                ->filterByMinPurchaseAmount($total, Criteria::LESS_THAN)
                ->filterByAmount($total, Criteria::LESS_THAN)
                ->filterByActiveFrom($now, Criteria::LESS_EQUAL)
                ->_or()
                    ->filterByActiveFrom(null, Criteria::ISNULL)
                ->filterByActiveTo($now, Criteria::GREATER_EQUAL)
                ->_or()
                    ->filterByActiveTo(null, Criteria::ISNULL)
                ->findOne()
            ;

            if (!$coupon instanceof Coupons) {
                $form->addError(new FormError('coupon.invalid.code'));

                if ($this->getFormat() == 'json') {
                    return $this->json_response(array(
                        'status'  => false,
                        'message' => $translator->trans('coupon.invalid.code', [], 'checkout'),
                    ));
                }

            } else {
                $discount = $coupon->getAmount();
                $coupon->setIsUsed(1);
                $coupon->save();

                $text = $translator->trans('coupon', [], 'checkout');
                $order->setDiscountLine($text, -$discount, 'coupon.code');
                $order->setAttribute('amount', 'coupon', $discount);
                $order->setAttribute('code', 'coupon', $coupon->getCode());
                $order->setAttribute('text', 'coupon', $text);
                $order->save();

                if ($this->getFormat() == 'json') {
                    return $this->json_response(array(
                        'status'  => true,
                        'message' => ''
                    ));
                }

                return $this->redirect($this->generateUrl('_checkout'));
            }

            if ($this->getFormat() == 'json') {
                return $this->json_response(array(
                    'status'  => false,
                    'message' => ''
                ));
            }
        }

        return $this->render('DiscountBundle:Coupon:form.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
