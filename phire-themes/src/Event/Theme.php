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
                $dir       = new Dir($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/themes/' . $theme->folder, ['filesOnly' => true]);
                $parentDir = null;
                if (null !== $theme->parent_id) {
                    $parentTheme = Table\Themes::findById($theme->parent_id);
                    if (isset($parentTheme->id)) {
                        $parentDir = new Dir($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/themes/' . $parentTheme->folder, ['filesOnly' => true]);
                    }
                }

                $forms = $application->config()['forms'];

                if (null !== $parentDir) {
                    $dirFiles = $dir->getFiles();
                    foreach ($dirFiles as $file) {
                        if ((strpos($file, '.ph') !== false) && (!in_array($file, $application->module('phire-themes')['invisible']))) {
                            $forms['Phire\Content\Form\Content'][0]['content_template']['value'][$file] = $file;
                        }
                    }
                    foreach ($parentDir->getFiles() as $file) {
                        if (!in_array($file, $dirFiles) && (strpos($file, '.ph') !== false) && (!in_array($file, $application->module('phire-themes')['invisible']))) {
                            $forms['Phire\Content\Form\Content'][0]['content_template']['value'][$file] = $file . ' (parent)';
                        }
                    }
                } else {
                    foreach ($dir->getFiles() as $file) {
                        if ((strpos($file, '.ph') !== false) && (!in_array($file, $application->module('phire-themes')['invisible']))) {
                            $forms['Phire\Content\Form\Content'][0]['content_template']['value'][$file] = $file;
                        }
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
        $template        = null;
        $themePath       = null;
        $parentThemePath = null;
        $realThemePath   = null;
        $theme           = Table\Themes::findBy(['active' => 1]);

        if (isset($theme->id)) {
            $themePath = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/themes/' . $theme->folder . '/';
            if (null !== $theme->parent_id) {
                $parentTheme = Table\Themes::findById($theme->parent_id);
                if (isset($parentTheme->id)) {
                    $parentThemePath = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/themes/' . $parentTheme->folder . '/';
                }
            }
        }

        if ($application->isRegistered('phire-content') &&
            ($controller instanceof \Phire\Content\Controller\IndexController) && ($controller->hasView())) {
            if (null !== $controller->getTemplate()) {
                if (isset($theme->id)) {
                    $controller->view()->themePath       = $themePath;
                    $controller->view()->parentThemePath = $parentThemePath;
                    if (($controller->getTemplate() == -1) &&
                        (file_exists($themePath . 'error.phtml') || file_exists($themePath . 'error.php'))) {
                        $template = file_exists($themePath . 'error.phtml') ? 'error.phtml' : 'error.php';
                    } else if (($controller->getTemplate() == -2) &&
                        (file_exists($themePath . 'date.phtml') || file_exists($themePath . 'date.php'))) {
                        $template = file_exists($themePath . 'date.phtml') ? 'date.phtml' : 'date.php';
                    } else if (file_exists($themePath . $controller->getTemplate())) {
                        $template = $controller->getTemplate();
                    }

                    $realThemePath = $themePath;

                    if ((null === $template) && (null !== $parentThemePath)) {
                        if (($controller->getTemplate() == -1) &&
                            (file_exists($parentThemePath . 'error.phtml') || file_exists($parentThemePath . 'error.php'))) {
                            $template = file_exists($parentThemePath . 'error.phtml') ? 'error.phtml' : 'error.php';
                        } else if (($controller->getTemplate() == -2) &&
                            (file_exists($parentThemePath . 'date.phtml') || file_exists($parentThemePath . 'date.php'))) {
                            $template = file_exists($parentThemePath . 'date.phtml') ? 'date.phtml' : 'date.php';
                        } else if (file_exists($parentThemePath . $controller->getTemplate())) {
                            $template = $controller->getTemplate();
                        }

                        $realThemePath = $parentThemePath;
                    }

                    if ((null !== $template) && (null !== $realThemePath)) {
                        $device = self::getDevice($controller->request()->getQuery('mobile'));
                        if ((null !== $device) && (file_exists($realThemePath . $device . '/' . $template))) {
                            $template = $device . '/' . $template;
                        }
                        $controller->view()->setTemplate($realThemePath . $template);
                    }
                }
            }
        }


    }

    /**
     * Method to determine the mobile device
     *
     * @param  string $mobile
     * @return string
     */
    public static function getDevice($mobile = null)
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
