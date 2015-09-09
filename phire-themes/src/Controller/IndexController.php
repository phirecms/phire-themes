<?php

namespace Phire\Themes\Controller;

use Phire\Themes\Model;
use Phire\Themes\Table;
use Phire\Controller\AbstractController;

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

        $this->send();
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

        $this->sess->setRequestValue('saved', true, 1);
        $this->redirect(BASE_PATH . APP_URI . '/themes');
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
            $this->sess->setRequestValue('removed', true, 1);
        } else {
            $this->sess->setRequestValue('saved', true, 1);
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
