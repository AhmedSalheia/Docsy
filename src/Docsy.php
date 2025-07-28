<?php

namespace Docsy;

use Docsy\Utility\Exporters\AbstractExporter;
use Docsy\Utility\Generators\AbstractGenerator;
use Docsy\Utility\Importers\AbstractImporter;
use Docsy\Utility\Traits\ArrayJsonSerialization;
use Docsy\Utility\Traits\HasCollections;
use Docsy\Utility\Traits\HasMeta;
use Exception;

class Docsy implements \JsonSerializable
{
    use ArrayJsonSerialization, HasCollections, HasMeta;

    private static ?Docsy $instance;

    private function __construct() {
        $this->addCollection();
    }

    /**
     * @param bool $force_new
     * @return Docsy
     */
    public static function getInstance(bool $force_new = false): static
    {
        if ($force_new) return new static();
        self::$instance = self::$instance ?? new static();
        return self::$instance;
    }

    /**
     * @param self $instance
     * @return Docsy
     */
    public static function setInstance(self $instance): Docsy
    {
        self::$instance = $instance;
        return self::$instance;
    }

    /**
     * Export All Collection a file, or multiple files
     *
     * @param string $formatter The Export Format
     * @param bool $single_file Optional, if the format is set to "json" save the whole docsy to a single file or to multiple files @Default: false
     * @param string|null $save_dir The dir to save files to
     * @return void
     * @throws Exception
     */
    public function export(string $formatter, array $options = [], bool $single_file = false, ?string $save_dir = null) : void
    {
        $formatters = config('docsy.formatters.exporters');
        $formatter_class = $formatters[$formatter] ?? null;
        if ($formatter_class == null)
            throw new Exception("Formatter '$formatter' not found");

        if ($save_dir == null) $save_dir = rtrim(config('docsy.export_path'),'/');

        if (!$single_file) {
            mkdir($save_dir . '/Docsy_'. date('Y_m_d_h_i_s'));
            foreach ($this->collections as $collection) {
                $collection->export($formatter,$options,$save_dir . '/Docsy_'. date('Y_m_d_h_i_s') );
            }
        } else {
            /* @var AbstractExporter $exporter */
            $exporter = new $formatter_class();

            $data = $exporter::export($this, options: $options);
            file_put_contents($save_dir . '/Docsy_'. date('Y_m_d_h_i_s') .'.' . $exporter::file_ext(), $data);
        }
    }
    /**
     * Export All Collection a file, or multiple files
     *
     * @param string $formatter The Generation Format
     * @param bool $single_file Optional, if the format is set to "json" save the whole docsy to a single file or to multiple files @Default: false
     * @param string|null $save_dir The dir to save files to
     * @return void
     * @throws Exception
     */
    public function generate(string $formatter, array $options = [], bool $single_file = true, ?string $save_dir = null) : void
    {
        $formatters = config('docsy.formatters.generators');
        $formatter_class = $formatters[$formatter] ?? null;
        if ($formatter_class == null)
            throw new Exception("Formatter '$formatter' not found");

        if ($save_dir == null) $save_dir = rtrim(config('docsy.generate_path'),'/');

        if (!$single_file) {
            mkdir($save_dir . '/Docsy_'. date('Y_m_d_h_i_s'));
            foreach ($this->collections as $collection) {
                $collection->generate($formatter,save_dir: $save_dir . '/Docsy_'. date('Y_m_d_h_i_s') );
            }
        } else {
            /* @var AbstractGenerator $generator */
            $generator = new $formatter_class();

            $save_path = $save_dir . '/Docsy_'. date('Y_m_d_h_i_s') .'.' . $generator::file_ext();
            $data = $generator::generate($this,options: $options);
            file_put_contents($save_path, $data);
        }
    }

    /**
     * import All Collection from a directory, or a docsy import json file
     *
     * @param string $formatter The importer to use, from docsy.formatters.importers
     * @param string $dir_or_file_path the path where the dir of collections to import or the docsy export json path
     * @return void
     * @throws Exception
     */
    public function import(string $formatter, string $dir_or_file_path, array $options = []) : void
    {
        $formatters = config('docsy.formatters.importers');
        $formatter_class = $formatters[$formatter] ?? null;

        if ($formatter_class == null)
            throw new Exception("Formatter '$formatter' not found");

        /* @var AbstractImporter $importer */
        $importer = new $formatter_class();

        if (!file_exists($dir_or_file_path))
            throw new Exception("Directory or file '$dir_or_file_path' not found");

        if (is_dir($dir_or_file_path))
            $files = array_map(
                fn ($file) => $dir_or_file_path . '/' .$file,
                array_values(
                    array_filter(
                        scandir($dir_or_file_path),
                        fn ($file) => is_file($dir_or_file_path . '/' .$file)
                    )
                )
            );
        else $files = [$dir_or_file_path];

        $importer::import($options, ...$files);
    }

    public function summary(): array
    {
        return array_map(fn($collection) => [
            'name' => $collection->name,
            'folders' => $collection->count(Folder::class),
            'requests' => $collection->count(Request::class),
            'auth_request' => 'no'
        ], $this->collections);
    }

    public function reset(): static
    {
        $this->collections = [];
        self::$instance = null;
        return $this;
    }
    public function toArray(): array
    {
        return [
            'class_name' => basename(get_class($this)),
            'meta' => $this->meta,
            'collections' => $this->collections(),
        ];
    }

    public static function fromArray(array $array, $force_new = false): static
    {
        $docsy = static::getInstance($force_new);
        $docsy->collections = Collection::fromArrayCollection(null, ...$array['collections']);
        $docsy->setMeta($array['meta']??'');
        return $docsy;
    }

    /**
     * @throws Exception
     */
    public static function fromArrayCollection(...$arrayCollections): void
    {
        throw new Exception('Cannot create Docsy collection');
    }
}
