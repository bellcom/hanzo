<?php /* vim: set sw=4: */

namespace Hanzo\Bundle\AccountBundle\Form\Type;

use Hanzo\Core\Hanzo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CustomersType extends AbstractType
{
    protected $isNew;
    protected $addressType;

    /**
     * @param bool          $isNew
     * @param AddressesType $addressType
     */
    public function __construct($isNew = true, AddressesType $addressType)
    {
        $this->addressType = $addressType;
        $this->isNew       = (boolean) $isNew;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @throws \Exception
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $domainKey = Hanzo::getInstance()->get('core.domain_key');
        $shortDomainKey = substr($domainKey, -2);

        if (in_array($shortDomainKey, ['DE'])) {
            $builder->add('title', 'choice', [
                'choices'  => [
                    'female' => 'title.female',
                    'male'   => 'title.male',
                ],
                'label'    => 'title',
                'required' => true,
                'trim'     => true
            ]);
        }

        $builder->add('first_name', null, ['trim' => true]);
        $builder->add('last_name', null, ['trim' => true]);

        $builder->add('addresses', 'collection', [
            'type' => $this->addressType,
            'attr' => ['autocomplete' => 'off'],
        ]);

        $builder->add('phone', null, [
            'required' => true,
            'attr'     => ['autocomplete' => 'off'],
        ]);

        $builder->add('email', 'repeated', [
            'type'            => 'email',
            'invalid_message' => 'email.invalid.match',
            'first_name'      => 'email_address',
            'second_name'     => 'email_address_repeated',
            'options'         => ['attr' => ['autocomplete' => 'off']],
        ]);

        $builder->add('password', 'repeated', [
            'type'            => 'password',
            'invalid_message' => 'password.invalid.match',
            'first_name'      => 'pass',
            'second_name'     => 'pass_repeated',
            'required'        => $this->isNew,
            'options'         => ['attr' => ['autocomplete' => 'off']],
        ]);

        if ($this->isNew) {
            $attr = [
                'autocomplete' => 'off',
                'checked'      => 'checked'
            ];

            // Disable default choice for NL, DK
            if (in_array($domainKey, ['NL', 'DK'])) {
                unset($attr['checked']);
            }

            $builder->add('newsletter', 'checkbox', [
                'label'    => 'create.newsletter',
                'required' => false,
                'mapped'   => false,
                'attr'     => $attr,
            ]);

            $builder->add('accept', 'checkbox', [
                'label'    => 'create.accept',
                'required' => true,
                'mapped'   => false,
                'attr'     => ['autocomplete' => 'off'],
            ]);
        }
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'account',
            'data_class'         => 'Hanzo\Model\Customers',
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'customers';
    }
}
