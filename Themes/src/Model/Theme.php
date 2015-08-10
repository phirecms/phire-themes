<?php

namespace Themes\Model;

use Themes\Table;
use Phire\Model\AbstractModel;
use Pop\Archive\Archive;
use Pop\File\Dir;

class Theme extends AbstractModel
{

    /**
     * Get all themes
     *
     * @param  string $sort
     * @return array
     */
    public function getAll($sort = null)
    {
        $order     = (null !== $sort) ? $this->getSortOrder($sort) : 'id ASC';
        $rows      = Table\Themes::findAll(null, ['order' => $order])->rows();
        $themePath = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/themes';

        foreach ($rows as $i => $row) {
            if (file_exists($themePath . '/' . $row->name . '/screenshot.jpg')) {
                $rows[$i]->screenshot = '<img class="theme-screenshot" src="' . BASE_PATH . CONTENT_PATH . '/themes/' . $row->name . '/screenshot.jpg" width="100" />';
            } else if (file_exists($themePath . '/' . $row->name . '/screenshot.png')) {
                $rows[$i]->screenshot = '<img class="theme-screenshot" src="' . BASE_PATH . CONTENT_PATH . '/themes/' . $row->name . '/screenshot.png" width="100" />';
            } else {
                $rows[$i]->screenshot = null;
            }
        }

        return $rows;
    }

    /**
     * Detect new themes
     *
     * @param  boolean $count
     * @return mixed
     */
    public function detectNew($count = true)
    {
        $themePath = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/themes';
        $installed = [];
        $newThemes = [];

        if (file_exists($themePath)) {
            $themes = Table\Themes::findAll();

            foreach ($themes->rows() as $theme) {
                $installed[] = $theme->file;
            }

            $dir = new Dir($themePath, false, false, false);
            foreach ($dir->getFiles() as $file) {
                if (((substr($file, -4) == '.zip') || (substr($file, -4) == '.tgz') ||
                        (substr($file, -7) == '.tar.gz')) && (!in_array($file, $installed))
                ) {
                    $newThemes[] = $file;
                }
            }
        }

        return ($count) ? count($newThemes) : $newThemes;
    }

    /**
     * Install themes
     *
     * @param  \Pop\Service\Locator $services
     * @throws \Exception
     * @return void
     */
    public function install(\Pop\Service\Locator $services)
    {
        $themePath = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/themes';
        $themes    = $this->detectNew(false);

        if (!is_writable($themePath)) {
            throw new \Phire\Exception('Error: The theme folder is not writable.');
        }

        $formats = Archive::getFormats();

        foreach ($themes as $theme) {
            if (file_exists($themePath . '/' . $theme)) {
                $ext  = null;
                $name = null;
                if (substr($theme, -4) == '.zip') {
                    $ext  = 'zip';
                    $name = substr($theme, 0, -4);
                } else if (substr($theme, -4) == '.tgz') {
                    $ext  = 'tgz';
                    $name = substr($theme, 0, -4);
                } else if (substr($theme, -7) == '.tar.gz') {
                    $ext  = 'tar.gz';
                    $name = substr($theme, 0, -7);
                }

                if ((null !== $ext) && (null !== $name) && array_key_exists($ext, $formats)) {
                    $archive = new Archive($themePath . '/' . $theme);
                    $archive->extract($themePath);
                    if ((stripos($theme, 'gz') !== false) && (file_exists($themePath . '/' . $name . '.tar'))) {
                        unlink($themePath . '/' . $name . '.tar');
                    }

                    if (file_exists($themePath . '/' . $name)) {
                        $style = null;
                        $info  = [];

                        // Check for a style sheet
                        if (file_exists($themePath . '/' . $name . '/style.css')) {
                            $style = $themePath . '/' . $name . '/style.css';
                        } else if (file_exists($themePath . '/' . $name . '/styles.css')) {
                            $style = $themePath . '/' . $name . '/styles.css';
                        } else if (file_exists($themePath . '/' . $name . '/css/style.css')) {
                            $style = $themePath . '/' . $name . '/css/style.css';
                        } else if (file_exists($themePath . '/' . $name . '/css/styles.css')) {
                            $style = $themePath . '/' . $name . '/css/styles.css';
                        }

                        // Get theme info from config file
                        if (null != $style) {
                            $info = $this->getInfo(file_get_contents($style));
                        }

                        // Save theme in the database
                        $thm = new Table\Themes([
                            'name'   => $name,
                            'file'   => $theme,
                            'folder' => $name,
                            'active' => 0,
                            'assets' => serialize([
                                'info' => $info
                            ])
                        ]);

                        $thm->save();
                    }
                }
            }
        }
    }

    /**
     * Process themes
     *
     * @param  array                $post
     * @param  \Pop\Service\Locator $services
     * @return void
     */
    public function process($post, \Pop\Service\Locator $services)
    {
        foreach ($post as $key => $value) {
            if (strpos($key, 'active') !== false) {
                $themes = Table\Themes::findall();
                foreach ($themes->rows() as $theme) {
                    $thm = Table\Themes::findById($theme->id);
                    $thm->active = 0;
                    $thm->save();
                }
                $theme = Table\Themes::findById((int)$value);
                if (isset($theme->id)) {
                    $theme->active = 1;
                    $theme->save();
                }
            }
        }

        if (isset($post['rm_themes']) && (count($post['rm_themes']) > 0)) {
            $this->uninstall($post['rm_themes'], $services);
        }
    }

    /**
     * Uninstall themes
     *
     * @param  array                $ids
     * @param  \Pop\Service\Locator $services
     * @return void
     */
    public function uninstall($ids, $services)
    {
        $themePath = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/themes';

        foreach ($ids as $id) {
            $theme = Table\Themes::findById((int)$id);
            if (isset($theme->id)) {
                // Remove the theme folder and files
                if (file_exists($themePath . '/' . $theme->folder)) {
                    $dir = new Dir($themePath . '/' . $theme->folder);
                    $dir->emptyDir(true);
                }

                // Remove the theme file
                if (file_exists($themePath . '/' . $theme->file) &&
                    is_writable($themePath . '/' . $theme->file)) {
                    unlink($themePath . '/' . $theme->file);
                }

                $theme->delete();
            }
        }
    }

    /**
     * Get theme info
     *
     * @param  string $style
     * @return array
     */
    protected function getInfo($style)
    {
        $info = [];

        if (strpos($style, '*/') !== false) {
            $styleHeader    = substr($style, 0, strpos($style, '*/'));
            $styleHeader    = substr($styleHeader, (strpos($styleHeader, '/*') + 2));
            $styleHeaderAry = explode("\n", $styleHeader);
            foreach ($styleHeaderAry as $line) {
                if (strpos($line, ':')) {
                    $ary = explode(':', $line);
                    if (isset($ary[0]) && isset($ary[1])) {
                        $key        = trim(str_replace('*', '', $ary[0]));
                        $value      = trim(str_replace('*', '', $ary[1]));
                        $info[$key] = $value;
                    }
                }
            }
        }

        return $info;
    }

}
