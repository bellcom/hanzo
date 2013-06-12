<?php

namespace Hanzo\Twig\Extension;

use Hanzo\Bundle\ServiceBundle\Services\TwigStringService;

use Twig_Environment;
use Twig_Extension;
use Twig_Function_Method;
use Twig_Function_Function;
use Twig_Filter_Method;

use Hanzo\Core\Hanzo;
use Hanzo\Core\Tools;

use Hanzo\Model\CmsI18nQuery;
use Hanzo\Model\OrdersPeer;

class MiscExtension extends Twig_Extension
{
    protected $twig_string;
    protected $settings;

    public function __construct(TwigStringService $twig_string)
    {
        $this->twig_string = $twig_string;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'misc';
    }


    /**
     * {@inheritdoc}
     */
    public function getGlobals()
    {
        return array(
            'layout' => $this->getLayout(),
        );
    }


    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'print_r' => new Twig_Function_Function('print_r'),
            'parse' => new Twig_Function_Method($this, 'parse', array('pre_escape' => 'html', 'is_safe' => array('html'))),
            'meta_tags' => new Twig_Function_Method($this, 'metaTags', array('pre_escape' => 'html', 'is_safe' => array('html'))),
            'google_analytics_tag' => new Twig_Function_Method($this, 'googleAnalyticsTag', array('pre_escape' => 'html', 'is_safe' => array('html'))),
            'front_page_teasers' => new Twig_Function_Method($this, 'frontPageTeasers', array('pre_escape' => 'html', 'is_safe' => array('html'))),
            'parameter' => new Twig_Function_Method($this, 'parameter', array('pre_escape' => 'html', 'is_safe' => array('html'))),
            'embed' => new Twig_Function_Method($this, 'embed', array('pre_escape' => 'html', 'is_safe' => array('html'), 'needs_environment' => true)),
        );
    }


    /**
     * {@inheritdoc}
     */
    public function getFilters() {
        return array(
            'money'  => new Twig_Filter_Method($this, 'moneyFormat'),
            'og_description' => new Twig_Filter_Method($this, 'ogDescription'),
        );
    }


    /**
     * Set the global twig var "layout"
     *
     * @see Hanzo\Core\HanzoBoot::onKernelRequest()
     * @return string
     */
    public function getLayout()
    {
        $hanzo = Hanzo::getInstance();
        $device = $hanzo->container->get('request')->attributes->get('_x_device');
        $mode = $hanzo->container->get('kernel')->getStoreMode();

        if ('webshop' == $mode) {
            $mode = '';
        }

        // TODO: implement the mobile layout
        $device_map = array(
            'pc' => 'base.html.twig',
            // 'mobile' => '::base_mobile.html.twig',
        );

        $layout = isset($device_map[$device]) ? $device_map[$device] : $device_map['pc'];

        return '::'.$mode.$layout;
    }


    /**
     * @see Hanzo\Core\Tools\Tools::moneyFormat
     * NICETO: loose the wrapper, figure out how to use namespaces and load the Tools class in the getF*() methods
     */
    public function moneyFormat($number, $format = '%.2i')
    {
        return Tools::moneyFormat($number, $format);
    }


    /**
     * Returns a "template string" parsed by Twig_String
     *
     * @param string $string
     * @param array $parameters
     * @return string
     */
    public function parse($string, $parameters = array())
    {
        $find = '~(background|src)="(../|/)~';
        $replace = '$1="' . Hanzo::getInstance()->get('core.cdn');
        $string = preg_replace($find, $replace, $string);

        return $this->twig_string->parse($string, $parameters);
    }


    /**
     * Returns any meta data associated with this domain.
     *
     * @param bool choose to include or exclude all OG tags
     * @return string
     */
     public function metaTags($includeOG = TRUE)
     {
         $meta = Hanzo::getInstance()->getByNs('meta');

         $result = '';
         foreach ($meta as $key => $value) {
            if(!$includeOG && 0 === strpos($key, 'og:'))
                continue;
             $attr = 'name';
             if (0 === strpos($key, 'og:')) {
                 $attr = 'property';
             }
             $result .= '<meta '.$attr.'="'.$key.'" content="'.$value.'">'."\n";
         }

         return $result;
     }


     /**
      * Google analytics tag, will only be displayed if a key is found
      */
     public function googleAnalyticsTag()
     {
        $google = Hanzo::getInstance()->getByNs('google');
        if (!empty($google['analytics_id'])) {
            return <<<DOC
<script type="text/javascript">
var _gaq = _gaq || [];
_gaq.push(['_setAccount', '{$google['analytics_id']}']);
_gaq.push(['_trackPageview']);
(function() {
  var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
  ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
  var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();
</script>
DOC;
        }

        return '';
     }


     /**
      * Build and return frontpage teasers
      *
      * TODO:
      * - implement caching
      * - implement templating
      * - fix hardcoded id
      */
     public function frontPageTeasers()
     {
        $pages = CmsI18nQuery::create()
            ->useCmsQuery()
                ->filterByCmsThreadId(21) // FIXME!
            ->endUse()
            ->findByLocale(Hanzo::getInstance()->get('core.locale'))
        ;

        ob_start();
        if ($pages->count()) {
?>
  <aside id="teasers" role="complementary">
    <ul>
<?php $i=1; foreach ($pages as $page): ?>
      <li class="teaser-box-<?php echo $i ?>">
        <?php echo $this->parse($page->getContent()) ?>
      </li>
<?php $i++; endforeach; ?>
    </ul>
  </aside>
<?php
        }

        return ob_get_clean();
     }


     /**
      * get a settings parameter
      *
      * @param  string $name       settings identifier
      * @param  array  $parameters array of replacement parameters
      * @return string
      */
     public function parameter($name, $parameters = array())
     {
        if (empty($this->settings)) {
            $this->settings = Hanzo::getInstance()->get('ALL');
        }

        $out = '';
        if (strpos($name, '.') === false) {
            foreach ($this->settings as $key => $value) {
                if (preg_match('/.'.$name.'$/i', $key)) {
                    $out = $value;
                }
            }
        } else {
            if (isset($this->settings[$name])) {
                $out = $this->settings[$name];
            }
        }

        return strtr($out, $parameters);
     }


     /**
      * embed html elements in cms pages
      *
      * @param  Twig_Environment $env        [description]
      * @param  string           $name       [description]
      * @param  array            $parameters [description]
      * @return string
      */
     public function embed(Twig_Environment $env, $name, $parameters = array())
     {
        switch ($name) {
            case 'newsletter_form':
                $view = '';
                $customer = null;
                if (isset($parameters['view']) && $parameters['view'] == 'simple') {
                    $view = 'simple-';
                } else {
                    $customer = \Hanzo\Model\CustomersPeer::getCurrent();
                }

                $template = 'NewsletterBundle:Default:'.$view.'block.html.twig';
                $parameters = array(
                    'customer' => $customer,
                    'listid' => Hanzo::getInstance()->container->get('newsletterapi')->getListIdAvaliableForDomain(),
                );

                break;

            // {{ embed('media_file', {
            //   'file': 'xhjkpiydjns/MissionVision.pdf',
            //   'date_format': 'long',
            //   'text': '» Mission, vision og idégrundlag (pdf)'
            // }) }}
            case 'media_file':
                $cdn = Hanzo::getInstance()->get('core.cdn');

                $parameters['file'] = isset($parameters['file']) ? $parameters['file'] : '';
                $parameters['text'] = isset($parameters['text']) ? $parameters['text'] : '';
                $parameters['date_format'] = isset($parameters['date_format']) ? $parameters['date_format'] : 'Y-m-d H:i';
                $parameters['date_label'] = isset($parameters['date_label']) ? $parameters['date_label'] : '';

                if (empty($parameters['file']) || empty($parameters['text'])) {
                    return '';
                }

                $ext = pathinfo($parameters['file'], PATHINFO_EXTENSION);

                if (empty($parameters['date_label'])) {
                    return '<a href="'.$cdn.'images/'.$parameters['file'].'" rel="external" class="media_file filetype-'.$ext.'">'.$parameters['text'].'</a>';
                }

                return '<a href="'.$cdn.'images/'.$parameters['file'].'" rel="external" class="media_file rewrite filetype-'.$ext.'" data-dateformat="'.$parameters['date_format'].'" data-datelabel="'.$parameters['date_label'].'">'.$parameters['text'].'</a> <em></em> ';

                break;

            case 'edit_warning':
                if (OrdersPeer::inEdit()) {
                    return Tools::getInEditWarning();
                }
                return '';

                break;
            case 'slideshow':
                if (isset($parameters['slides'])) {
                    $class = (!empty($parameters['class']))?' '.$parameters['class']:' grid_6';
                    $html = '<div class="cycle-slideshow '.$class.'" data-cycle-slides="> a" data-pause-on-hover="true">';
                    foreach ($parameters['slides'] as $slide) {
                        $html .= $slide;
                    }
                    $html .= '<div class="cycle-pager"></div></div>';
                    return $html;
                }
                break;
        }

        $html = '';
        try {
            $html = $env->render($template, $parameters);
        } catch (\Exception $e) {
            Tools::log($e->getMessage());
        }

        return $html;
    }

    public function ogDescription($description)
    {
        $description = explode('<br>', $description);
        $description = trim($description[0]);

        return $description;
    }
}
