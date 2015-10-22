<?php

namespace Phire\Themes\Model;

use Phire\Themes\Table;
use Phire\Model\AbstractModel;
use Pop\Archive\Archive;
use Pop\File\Dir;
use Pop\Http\Client\Curl;

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
        $rows      = Table\Themes::findAll(['order' => $order])->rows();
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
     * Get theme by ID
     *
     * @param  int $id
     * @return void
     */
    public function getById($id)
    {
        $theme = Table\Themes::findById($id);
        if (isset($theme->id)) {
            $data = $theme->getColumns();
            $data['assets'] = unserialize($data['assets']);
            $this->data = array_merge($this->data, $data);
        }
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
     * Detect new child themes
     *
     * @param  boolean $count
     * @return mixed
     */
    public function detectChildren($count = true)
    {
        $themePath   = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/themes';
        $installed   = [];
        $newChildren = [];

        if (file_exists($themePath)) {
            $themes = Table\Themes::findAll();

            foreach ($themes->rows() as $theme) {
                $installed[] = $theme->folder;
            }

            foreach ($installed as $folder) {
                if (file_exists($themePath . '/' . $folder . '-child') && !in_array($folder . '-child', $installed)) {
                    $newChildren[$folder] = $folder . '-child';
                }
            }
        }

        return ($count) ? count($newChildren) : $newChildren;
    }

    /**
     * Install themes
     *
     * @throws \Exception
     * @return void
     */
    public function install()
    {
        $themePath = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/themes';
        $themes    = $this->detectNew(false);
        $children  = $this->detectChildren(false);

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
                        $style   = null;
                        $info    = [];
                        $version = 'N/A';

                        // Check for a style sheet
                        if (file_exists($themePath . '/' . $name . '/style.css')) {
                            $style = $themePath . '/' . $name . '/style.css';
                        } else if (file_exists($themePath . '/' . $name . '/styles.css')) {
                            $style = $themePath . '/' . $name . '/styles.css';
                        } else if (file_exists($themePath . '/' . $name . '/css/style.css')) {
                            $style = $themePath . '/' . $name . '/css/style.css';
                        } else if (file_exists($themePath . '/' . $name . '/css/styles.css')) {
                            $style = $themePath . '/' . $name . '/css/styles.css';
                        } else if (file_exists($themePath . '/' . $name . '/style/style.css')) {
                            $style = $themePath . '/' . $name . '/style/style.css';
                        } else if (file_exists($themePath . '/' . $name . '/style/styles.css')) {
                            $style = $themePath . '/' . $name . '/style/styles.css';
                        } else if (file_exists($themePath . '/' . $name . '/styles/style.css')) {
                            $style = $themePath . '/' . $name . '/styles/style.css';
                        } else if (file_exists($themePath . '/' . $name . '/styles/styles.css')) {
                            $style = $themePath . '/' . $name . '/styles/styles.css';
                        }

                        // Get theme info from config file
                        if (null != $style) {
                            $info = $this->getInfo(file_get_contents($style));
                            if (isset($info['version'])) {
                                $version = $info['version'];
                            } else if (isset($info['Version'])) {
                                $version = $info['Version'];
                            } else if (isset($info['VERSION'])) {
                                $version = $info['VERSION'];
                            }
                        }

                        // Save theme in the database
                        $thm = new Table\Themes([
                            'name'    => $name,
                            'file'    => $theme,
                            'folder'  => $name,
                            'version' => $version,
                            'active'  => 0,
                            'assets'  => serialize([
                                'info' => $info
                            ]),
                            'installed_on' => date('Y-m-d H:i:s')
                        ]);

                        $thm->save();

                        $this->sendStats($name, $version);
                    }
                }
            }
        }

        foreach ($children as $parent => $child) {
            $parentTheme = Table\Themes::findBy(['folder' => $parent]);

            if (isset($parentTheme->id) && file_exists($themePath . '/' . $child)) {
                $style = null;
                $info  = [];

                // Check for a style sheet
                if (file_exists($themePath . '/' . $child . '/style.css')) {
                    $style = $themePath . '/' . $child . '/style.css';
                } else if (file_exists($themePath . '/' . $child . '/styles.css')) {
                    $style = $themePath . '/' . $child . '/styles.css';
                } else if (file_exists($themePath . '/' . $child . '/css/style.css')) {
                    $style = $themePath . '/' . $child . '/css/style.css';
                } else if (file_exists($themePath . '/' . $child . '/css/styles.css')) {
                    $style = $themePath . '/' . $child . '/css/styles.css';
                }

                // Get theme info from config file
                if (null != $style) {
                    $info = $this->getInfo(file_get_contents($style));
                }

                // Save theme in the database
                $thm = new Table\Themes([
                    'parent_id' => $parentTheme->id,
                    'name'      => $child,
                    'folder'    => $child,
                    'active'    => 0,
                    'assets'    => serialize([
                        'info' => $info
                    ])
                ]);

                $thm->save();
            }
        }
    }

    /**
     * Get theme update
     *
     * @param  string $theme
     * @return void
     */
    public function getUpdate($theme)
    {
        if (file_exists(__DIR__ . '/../../../../themes/' . $theme . '.zip')) {
            unlink(__DIR__ . '/../../../../themes/' . $theme . '.zip');
        }

        if (file_exists(__DIR__ . '/../../../../themes/' . $theme)) {
            $dir = new Dir(__DIR__ . '/../../../../themes/' . $theme);
            $dir->emptyDir(true);
        }

        file_put_contents(
            __DIR__ . '/../../../../themes/' . $theme . '.zip',
            fopen('http://updates.phirecms.org/releases/themes/' . $theme . '.zip', 'r')
        );

        $basePath = realpath(__DIR__ . '/../../../../themes/');
        $archive = new Archive($basePath . '/' . $theme . '.zip');
        $archive->extract($basePath);

        $theme = Table\Themes::findById($this->id);
        $theme->updated_on = date('Y-m-d H:i:s');
        $theme->save();
    }

    /**
     * Process themes
     *
     * @param  array $post
     * @return void
     */
    public function process($post)
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
            $this->uninstall($post['rm_themes']);
        }
    }

    /**
     * Uninstall themes
     *
     * @param  array $ids
     * @return void
     */
    public function uninstall($ids)
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

                $children = Table\Themes::findBy(['parent_id' => $theme->id]);
                if ($children->hasRows()) {
                    foreach ($children->rows() as $child) {
                        $childThemePath = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/themes/' . $child->folder;

                        // Remove the child theme folder and files
                        if (file_exists($childThemePath)) {
                            $dir = new Dir($childThemePath);
                            $dir->emptyDir(true);
                        }

                        $c = Table\Themes::findById($child->id);
                        if (isset($c->id)) {
                            $c->delete();
                        }
                    }
                }

                $theme->delete();
            }
        }
    }

    /**
     * Get update info
     *
     * @return \ArrayObject
     */
    public function getUpdates()
    {
        $themeUpdates = [];
        $headers      = [
            'Authorization: ' . base64_encode('phire-updater-' . time()),
            'User-Agent: ' . (isset($_SERVER['HTTP_USER_AGENT']) ?
                $_SERVER['HTTP_USER_AGENT'] : 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:41.0) Gecko/20100101 Firefox/41.0')
        ];

        $themes = Table\Themes::findAll();
        if ($themes->hasRows()) {
            foreach ($themes->rows() as $theme) {
                $curl = new Curl('http://updates.phirecms.org/latest/' . $theme->folder . '?theme=1', [
                    CURLOPT_HTTPHEADER => $headers
                ]);
                $curl->send();

                if ($curl->getCode() == 200) {
                    $json = json_decode($curl->getBody(), true);
                    $themeUpdates[$theme->folder] = $json['version'];
                }
            }
        }

        return $themeUpdates;
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

    /**
     * Send installation stats
     *
     * @param  string $name
     * @param  string $version
     * @return void
     */
    protected function sendStats($name, $version)
    {
        $headers = [
            'Authorization: ' . base64_encode('phire-stats-' . time()),
            'User-Agent: ' . (isset($_SERVER['HTTP_USER_AGENT']) ?
                $_SERVER['HTTP_USER_AGENT'] : 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:41.0) Gecko/20100101 Firefox/41.0')
        ];

        $curl = new Curl('http://stats.phirecms.org/theme', [
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $curl->setPost(true);
        $curl->setFields([
            'name'      => $name,
            'version'   => $version,
            'domain'    => (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ''),
            'ip'        => (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : ''),
            'os'        => PHP_OS,
            'server'    => (isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : ''),
            'php'       => PHP_VERSION,
            'db'        => DB_INTERFACE . ((DB_INTERFACE == 'pdo') ? ' (' . DB_TYPE . ')' : '')
        ]);

        $curl->send();
    }

}
