<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace cms\classes\cmd;

use wulaphp\app\App;
use wulaphp\artisan\ArtisanCommand;
use wulaphp\io\Storage;

/**
 * 存储迁移命令.
 *
 * @package cms\classes\cmd
 */
class StorageMigrateCommand extends ArtisanCommand {
    private $ssn;

    public function cmd() {
        return 'storage:migrate';
    }

    public function desc() {
        return 'migrate file from one storage to other';
    }

    protected function paramValid(/** @noinspection PhpUnusedParameterInspection */ $options) {
        $this->ssn = $this->opt();
        if (!$this->ssn) {
            $this->help();
            exit(1);
        }

        return true;
    }

    protected function execute($options) {
        try {
            $storage  = new Storage($this->ssn);
            $oldSsn   = App::cfg('storage@cms', 'file:path=storage');
            $storage1 = new Storage($oldSsn);
            $i        = 0;
            $limit    = 200;
            $total    = 0;
            $db       = App::db();
            while (true) {
                $start = $i * $limit;
                $files = $db->query('SELECT data_file FROM {cms_page_rev} ORDER BY page_id ASC,ver ASC LIMIT %d,%d', $start, $limit);
                if (!$files) {
                    break;
                }
                $this->log('migrate ' . $start . ' to ' . ($start + $limit) . ' ... ', false);
                foreach ($files as $f) {
                    $storage->save($f['data_file'], $storage1->load($f['data_file']));
                }
                $total ++;
                $this->log('done');
                $i ++;
            }
            $this->log('Done!');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            exit(1);
        }

        return 0;
    }

    protected function argDesc() {
        return '<ssn>';
    }

    public static function artisanGetCommands($cmds) {
        $cmds['storage:migrate'] = new self();

        return $cmds;
    }
}