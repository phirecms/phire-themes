<?php

namespace Phire\Themes\Event;

use Phire\Themes\Table;
use Pop\Application;
use Pop\File\Dir;
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
        if ($application->isRegistered('phire-content')) {
            $theme = Table\Themes::findBy(['active' => 1]);
            if (isset($theme->id)) {
                $dir = new Dir($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/themes/' . $theme->folder, false, false, false);
                $forms = $application->config()['forms'];
                foreach ($dir->getFiles() as $file) {
                    if ((strpos($file, '.ph') !== false) && (self::checkTemplateName($file))) {
                        $forms['Phire\Content\Form\Content'][0]['content_template']['value'][$file] = $file;
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
        $template  = null;
        $themePath = null;
        $theme     = Table\Themes::findBy(['active' => 1]);

        if (isset($theme->id)) {
            $themePath = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/themes/' . $theme->folder . '/';
        }

        if ($application->isRegistered('phire-categories') &&
            ($controller instanceof \Phire\Categories\Controller\IndexController) && ($controller->hasView())) {
            if (isset($theme->id) && (file_exists($themePath . 'category.phtml') || file_exists($themePath . 'category.php'))) {
                $template = file_exists($themePath . 'category.phtml') ? 'category.phtml' : 'category.php';
            }
        } else if ($application->isRegistered('phire-content') &&
            ($controller instanceof \Phire\Content\Controller\IndexController) && ($controller->hasView())) {
            if (null !== $controller->getTemplate()) {
                if (isset($theme->id)) {
                    if (($controller->getTemplate() == -1) &&
                        (file_exists($themePath . 'error.phtml') || file_exists($themePath . 'error.php'))) {
                        $template = file_exists($themePath . 'error.phtml') ? 'error.phtml' : 'error.php';
                    } else if (($controller->getTemplate() == -2) &&
                        (file_exists($themePath . 'date.phtml') || file_exists($themePath . 'date.php'))) {
                        $template = file_exists($themePath . 'date.phtml') ? 'date.phtml' : 'date.php';
                    } else if (file_exists($themePath . $controller->getTemplate())) {
                        $template = $controller->getTemplate();
                    }
                }
            }
        }

        if ((null !== $template) && (null !== $themePath)) {
            $device = self::getDevice($controller->request()->getQuery('mobile'));
            if ((null !== $device) && (file_exists($themePath . $device . '/' . $template))) {
                $template = $device . '/' . $template;
            }
            $controller->view()->setTemplate($themePath . $template);
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
        $result = false;
        if ((strtolower($name) != 'search') && (stripos($name, 'search.ph') === false) &&
            (strtolower($name) != 'sidebar') && (stripos($name, 'sidebar.ph') === false) && (stripos($name, 'sidebar-') === false) &&
            (strtolower($name) != 'category') && (stripos($name, 'category.ph') === false) && (stripos($name, 'category-') === false)  &&
            (strtolower($name) != 'date') && (stripos($name, 'date.ph') === false) &&
            (strtolower($name) != 'functions') && (stripos($name, 'functions.ph') === false) &&
            (strtolower($name) != 'error') && (stripos($name, 'error.ph') === false) &&
            (strtolower($name) != 'header') && (stripos($name, 'header.ph') === false) &&
            (strtolower($name) != 'footer') && (stripos($name, 'footer.ph') === false)) {
            $result = true;
        }

        return $result;
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
