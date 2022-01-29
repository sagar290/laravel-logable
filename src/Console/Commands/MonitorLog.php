<?php

namespace Sagar290\Logable\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Support\Arrayable;
use Symfony\Component\Console\Helper\Table;


class MonitorLog extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:monitor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'incoming Http request logs';
    /**
     * @var string
     */


    private $file = 'logs/laravel.log';

    public $renderedTable;


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->file = storage_path(config('logging.channels.logable.path', 'logs/laravel.log'));

        // if log drive is set to daily, then we need to append the date to the file name
        if (config('logging.channels.logable.driver') == 'daily') {
            $file = explode('.', config('logging.channels.logable.path', 'logs/laravel.log'));
            $this->file = $file[0] . '-' . date('Y-m-d') . '.' . $file[1];
        }

    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $version = "";

        try {
            while (true) {
                clearstatcache();
                $modifiedTs = filemtime($this->file);
                if ($modifiedTs > $version) {
                    $version = $modifiedTs;
                    $this->showLog();
                }
                sleep(config('logable.interval', 1));
            }
        } catch (Exception $e) {
            $this->error($e->getMessage());
            sleep(config('logable.retry_interval', 10));
            $this->handle();
        }
    }

    public function showLog()
    {
        $line = '';

        $f = fopen($this->file, 'r');
        $cursor = -1;

        fseek($f, $cursor, SEEK_END);
        $char = fgetc($f);

        /**
         * Trim trailing newline chars of the file
         */
        while ($char === "\n" || $char === "\r") {
            fseek($f, $cursor--, SEEK_END);
            $char = fgetc($f);
        }

        /**
         * Read until the start of file or first newline char
         */
        while ($char !== false && $char !== "\n" && $char !== "\r") {
            /**
             * Prepend the new char
             */
            $line = $char . $line;
            fseek($f, $cursor--, SEEK_END);
            $char = fgetc($f);
        }

        fclose($f);

        $lineBreakDown = explode(' ', $line);

        if (count($lineBreakDown) > 3) {

            $message = $this->getDecodeMessage($line);

            $columnFromMessage = $this->getColumnFromMessage($message);

            $valuesFromMessage = $this->sanitizeMessage($this->getValuesFromMessage($message));

            $this->clearPreviousPrintFromTerminal();

            if (!$this->renderedTable) {
                $this->renderedTable = $this->table([
                    'time',
                    ...$columnFromMessage
                ], [
                    [
                        $lineBreakDown[0] . ' ' . $lineBreakDown[1],
                        ...$valuesFromMessage
                    ]
                ], 'box');

                return;
            }


            $this->renderedTable->addRow([
                $lineBreakDown[0] . ' ' . $lineBreakDown[1],
                ...$valuesFromMessage
            ]);

            $this->renderedTable->render();

        }

    }

    public function getMessageData($message)
    {

        // regular expression match json string from a string
        preg_match('/{(?:[^{}]*|(?R))*}/', $message, $matches);

        return $matches[0] ?? [];
    }

    public function getDecodeMessage($message)
    {
        return json_decode($this->getMessageData($message), true);
    }


    private function getColumnFromMessage($message)
    {
        // extract all keys from array
        return is_array($message) ? array_keys($message) : [];
    }

    private function getValuesFromMessage($message)
    {
        // extract all values from array
        return is_array($message) ? array_values($message) : [];
    }

    private function sanitizeMessage(array $messages)
    {
        $validData = [];
        // validate message
        foreach ($messages as $message) {

            if (!is_string($message)) {
                $validData[] = json_encode($message, JSON_PRETTY_PRINT);
                continue;
            }

            $validData[] = $message;
        }

        return $validData;
    }

    /**
     * Format input to textual table.
     *
     * @param array $headers
     * @param Arrayable|array $rows
     * @param string $tableStyle
     * @param array $columnStyles
     */
    public function table($headers, $rows, $tableStyle = 'symfony-style-guide', array $columnStyles = [])
    {
        $table = new Table($this->output);

        if ($rows instanceof Arrayable) {
            $rows = $rows->toArray();
        }

        $table->setHeaders((array)$headers)->addRows($rows)->setStyle($tableStyle);

        foreach ($columnStyles as $columnIndex => $columnStyle) {
            $table->setColumnStyle($columnIndex, $columnStyle);
        }

        $table->render();

        return $table;

    }

    public function clearPreviousPrintFromTerminal()
    {
        $this->output->write(sprintf("\033\143"));
    }

}
