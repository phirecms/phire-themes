<?php

namespace Themes\Controller;

use Themes\Model;
use Themes\Table;
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
        $this->view->title     = 'Themes';
        $this->view->newThemes = $theme->detectNew();
        $this->view->themes    = $theme->getAll($this->request->getQuery('sort'));

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
        $theme->install($this->services);

        $this->redirect(BASE_PATH . APP_URI . '/themes?saved=' . time());
    }

    /**
     * Process action method
     *
     * @return void
     */
    public function process()
    {
        $theme = new Model\Theme();
        $theme->process($this->request->getPost(), $this->services);

        $uri = (null !== $this->request->getPost('rm_themes')) ?
            BASE_PATH . APP_URI . '/themes?removed=' . time() :
            BASE_PATH . APP_URI . '/themes?saved=' . time();

        \Pop\Http\Response::redirect($uri);
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
