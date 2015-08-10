<?php

namespace Themes\Event;

use Themes\Table;
use Pop\Application;
use Pop\Web\Mobile;
use Pop\Web\Session;
use Phire\Controller\AbstractController;

class Theme
{

    /**
     * Bootstrap the module
     *
     * @param  Application $application
     * @return void
     */
    public static function bootstrap(Application $application)
    {
        if ($application->isRegistered('Content')) {
            $templates = Table\Templates::findBy(['parent_id' => null]);
            if ($templates->hasRows()) {
                $forms  = $application->config()['forms'];
                foreach ($templates->rows() as $template) {
                    if (self::checkTemplateName($template->name)) {
                        $forms['Content\Form\Content'][0]['content_template']['value'][$template->id] = $template->name;
                    }
                }
                $application->mergeConfig(['forms' => $forms], true);
            }
        }
    }

    /**
     * Set the template for the content
     *
     * @param  AbstractController $controller
     * @param  Application        $application
     * @return void
     */
    public static function setTemplate(AbstractController $controller, Application $application)
    {
        if ($application->isRegistered('Content') &&
            ($controller instanceof \Content\Controller\IndexController) && ($controller->hasView())) {
            if (!is_numeric($controller->getTemplate())) {
                if ($controller->getTemplate() == -1) {
                    $template = Table\Templates::findBy(['name' => 'Error']);
                } else if ($controller->getTemplate() == -2) {
                    $template = Table\Templates::findBy(['name' => 'Date']);
                } else {
                    $template = Table\Templates::findById((int)$controller->getTemplate());
                }
                if (isset($template->id)) {
                    $device = self::getDevice($controller->request()->getQuery('mobile'));
                    if ((null !== $device) && ($template->device != $device)) {
                        $childTemplate = Table\Templates::findBy(['parent_id' => $template->id, 'device' => $device]);
                        if (isset($childTemplate->id)) {
                            $tmpl = $childTemplate->template;
                        } else {
                            $tmpl = $template->template;
                        }
                    } else {
                        $tmpl = $template->template;
                    }
                    $controller->view()->setTemplate(self::parse($tmpl));
                }
            }
        }
    }

    /**
     * Check if the template is allowed
     *
     * @param  string $name
     * @return boolean
     */
    public static function checkTemplateName($name)
    {
        $templates = [
            'date', 'error', 'footer', 'header', 'sidebar'
        ];

        return (!in_array(strtolower($name), $templates));
    }

    /**
     * Method to determine the mobile device
     *
     * @param  string $mobile
     * @return string
     */
    protected static function getDevice($mobile = null)
    {
        $session = Session::getInstance();

        if (null !== $mobile) {
            $force = $mobile;
            if ($force == 'clear') {
                unset($session->mobile);
            } else {
                $session->mobile = $force;
            }
        }

        if (!isset($session->mobile)) {
            $device = Mobile::getDevice();
            if (null !== $device) {
                $device = strtolower($device);
                if (($device == 'android') || ($device == 'windows')) {
                    $device .= (Mobile::isTabletDevice()) ? '-tablet' : '-phone';
                }
            }
        } else {
            $device = $session->mobile;
        }

        return $device;
    }

}
