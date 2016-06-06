<?php
/**
 * Phire Themes Module
 *
 * @link       https://github.com/phirecms/phire-themes
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Phire\Themes\Controller;

use Phire\Themes\Model;
use Phire\Themes\Table;
use Phire\Controller\AbstractController;

/**
 * Themes Index Controller class
 *
 * @category   Phire\Themes
 * @package    Phire\Themes
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    1.0.0
 */
class IndexController extends AbstractController
{

    /**
     * Index action method
     *
     * @return void
     */
    public function index()
    {
        $theme = new Model\Theme();

        $this->prepareView('themes/index.phtml');
        $this->view->title       = 'Themes';
        $this->view->newThemes   = $theme->detectNew();
        $this->view->newChildren = $theme->detectChildren();
        $this->view->themes      = $theme->getAll($this->request->getQuery('sort'));

        if (!isset($this->sess->updates->themes)) {
            $this->sess->updates->themes = $theme->getUpdates($this->application->module('phire-themes')->config()['updates']);
        }

        $this->view->themeUpdates = $this->sess->updates->themes;

        $this->send();
    }

    /**
     * Upload action method
     *
     * @return void
     */
    public function upload()
    {
        if (($_FILES) && !empty($_FILES['upload_theme']) && !empty($_FILES['upload_theme']['name'])) {
            $theme = new Model\Theme();
            $theme->upload($_FILES['upload_theme']);
            $theme->install();
            $this->sess->setRequestValue('saved', true);
        }

        $this->redirect(BASE_PATH . APP_URI . '/themes');
    }

    /**
     * Install action method
     *
     * @return void
     */
    public function install()
    {
        $theme = new Model\Theme();
        $theme->install();

        $this->sess->setRequestValue('saved', true);
        $this->redirect(BASE_PATH . APP_URI . '/themes');
    }

    /**
     * Update action method
     *
     * @param  int  $id
     * @return void
     */
    public function update($id)
    {
        $theme = new Model\Theme();
        $theme->getById($id);

        if (isset($theme->id) && isset($this->sess->updates->themes[$theme->name]) &&
            (version_compare($theme->version, $this->sess->updates->themes[$theme->name]) < 0)) {

            $this->prepareView('themes/update.phtml');

            if (($this->request->getQuery('update') == 1) &&
                is_writable(__DIR__ . '/../../../../themes') &&
                is_writable(__DIR__ . '/../../../../themes/' . $theme->folder) &&
                is_writable(__DIR__ . '/../../../../themes/' . $theme->folder . '.zip')) {

                $name    = $theme->folder;
                $version = substr($name, (strrpos($name, '-') + 1));
                if (is_numeric($version)) {
                    $name = substr($name, 0, (strrpos($name, '-')));
                }

                $new = $name . '-' . $this->sess->updates->themes[$theme->name];
                $old = $name . '-' . $theme->version;

                $theme->getUpdate($new, $old, $this->sess->updates->themes[$theme->name]);

                $this->view->title      = 'Update Theme ' . $name . ' : Complete!';
                $this->view->complete   = true;
                $this->view->theme_name = $theme->name;
                $this->view->version    = $theme->version;
            } else {
                $this->view->title = 'Update ' . $theme->name;
                $this->view->theme_id             = $theme->id;
                $this->view->theme_name           = $theme->name;
                $this->view->theme_update_version = $this->sess->updates->themes[$theme->name];

                if (is_writable(__DIR__ . '/../../../../themes') &&
                    is_writable(__DIR__ . '/../../../../themes/' . $theme->folder) &&
                    is_writable(__DIR__ . '/../../../../themes/' . $theme->folder . '.zip')) {
                    $this->view->writable = true;
                } else {
                    $this->view->writable = false;
                }
            }
            $this->send();
        } else {
            $this->redirect(BASE_PATH . APP_URI . '/themes');
        }
    }

    /**
     * Process action method
     *
     * @return void
     */
    public function process()
    {
        $theme = new Model\Theme();
        $theme->process($this->request->getPost());

        if (null !== $this->request->getPost('rm_themes')) {
            $this->sess->setRequestValue('removed', true);
        } else {
            $this->sess->setRequestValue('saved', true);
        }

        \Pop\Http\Response::redirect(BASE_PATH . APP_URI . '/themes');
        exit();
    }

    /**
     * Prepare view
     *
     * @param  string $theme
     * @return void
     */
    protected function prepareView($theme)
    {
        $this->viewPath = __DIR__ . '/../../view';
        parent::prepareView($theme);
    }

}
