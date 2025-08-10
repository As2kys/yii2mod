<?php

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use app\models\NginxLog;
use Yii;

class NginxController extends Controller
{
    /**
     * @return int Exit code
     */
    public function actionImport(
        $zipFileName = 'modimio.access.log.1.zip',
        $logFileName = 'modimio.access.log.1'
    ) {
        if (is_readable(getcwd() . '/web/logs/' . $zipFileName)
            && filesize(getcwd() . '/web/logs/' . $zipFileName)) {
            echo 'Importiong ' , $zipFileName, "...\n";

            $rows = [];
            $datesCount = [];

            $zipfile = getcwd() . '/web/logs/' . $zipFileName;
            $logfile = 'modimio.access.log.1';
            $tmpdir = sys_get_temp_dir() . '/';

            $zip = new \ZipArchive; // php memory_limit 512M min?
            if ($zip->open($zipfile) === true) {
                $c = 0;
                $zip->extractTo($tmpdir);
                if (!($handle = @fopen($tmpdir . $logFileName, 'r'))) {
                    echo "Log file is missing?\n";
                    return ExitCode::NOINPUT;
                }
                while (($line = fgets($handle)) !== false) {
                    $data = explode(' - - ', $line);
                    $data['ip'] = $data[0];
                    $data['datetime'] = str_replace(']', '',
                        preg_match('/\[(.*)\]/', $data[1], $matches) ? $matches[1] : ''
                    );
                    if ($q = strpos($data['datetime'], '"')) $data['datetime'] = substr($data['datetime'], 0, $q);

                    $data['date'] = date('d.m', strtotime($data['datetime']));
                    if ($c > 1155) if (isset($datesCount[$data['date']])) {
                        $datesCount[$data['date']]++;
                    } else {
                        if (strtotime($data['datetime']) > 0) $datesCount[$data['date']] = 1;
                    }

                    $data['url'] = substr(
                        preg_match('/\"(GET .*)\" 200/', $data[1], $matches) ? substr($matches[1], 4, -9) : '', 0, 250
                    );
                    $data['useragent'] = strpos($data[1], 'Googlebot')
                        ? substr($data[1], strpos($data[1], 'Googlebot'), -2)
                        : substr($data[1], strpos($data[1], 'Mozilla'), -2);
                    $data['x64'] = strpos($data['useragent'], 'x64') ? 'x64'
                        : (
                            (strpos($data['useragent'], 'x86') ||
                            strpos($data['useragent'], 'NT 5') ||
                            strpos($data['useragent'], 'NT 6')
                            ) ? 'x86' : '-');

                    if (strpos($data['useragent'], "Windows")) $data['os'] = 'Windows';
                    elseif (strpos($data['useragent'], "Mac OS") || strpos($data['useragent'], "Macintosh")) $data['os'] = "macOS";
                    elseif (strpos($data['useragent'], "Android")) $data['os'] = "Android";
                    elseif (strpos($data['useragent'], "iOS") || strpos($data['useragent'], "iPhone")
                        || strpos($data['useragent'], "iPad")) $data['os'] = "iOS";
                    elseif (strpos($data['useragent'], "Linux")) $data['os'] = "Linux";
                    else $data['os'] = '';

                    try {
                        if ($browser = get_browser($data['useragent'], true) && 0) {
                            $data['browser'] = $browser['browser'] ?? '-';
                        }
                    } catch (\Exception $ex) {
                        if (strpos($data['useragent'], 'Opera') || strpos($data['useragent'], 'OPR/')) $data['browser'] = 'Opera';
                        elseif (strpos($data['useragent'], 'Edge')) $data['browser'] = 'Edge';
                        elseif (strpos($data['useragent'], 'Chrome')) $data['browser'] = 'Chrome';
                        elseif (strpos($data['useragent'], 'Googlebot')) $data['browser'] = 'Googlebot';
                        elseif (strpos($data['useragent'], 'Safari')) $data['browser'] = 'Safari';
                        elseif (strpos($data['useragent'], 'Firefox')) $data['browser'] = 'Firefox';
                        elseif (strpos($data['useragent'], 'MSIE')) $data['browser'] = 'Internet Explorer';
                        else $data['browser'] = '-';
                    }

                    if (strlen($data['datetime']) > 30) $data['datetime'] = substr($data['datetime'], 0, 30);
                    if (strlen($data['useragent']) > 250) $data['useragent'] = substr($data['useragent'], 0, 250) . '...';
                    $rows[] = array_slice($data, 2);
                }
                if (count($rows)) {
                    Yii::$app->db->createCommand()->truncateTable('nginx_log')->execute();
                    $batchLen = 10000;
                    $batchStop = count($rows);
                    for ($i = 0; ($i * $batchLen) <= $batchStop; $i++) {
                        $command = Yii::$app->db
                            ->createCommand()
                            ->batchInsert(
                                'nginx_log',
                                ['ip', 'datetime', 'date', 'url', 'useragent', 'x64', 'os', 'browser'],
                                array_slice($rows, $batchLen * $i, $batchLen)
                            );
                        $command->execute();
                        echo '.';
                    }
                }
                else {
                    echo "No rows found?\n";
                    return ExitCode::DATAERR;
                }
            }
            else {
                echo "Zip opening error\n";
                return ExitCode::NOINPUT;
            }

            echo "\nThe end.\n";
            return ExitCode::OK;
        }
        else {
            exit("Input file {$zipFileName} is missing?\n");
        }

        echo "\nInput zip was:\n" , $zipFileName . "\n";
    }
}