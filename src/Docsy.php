<?php

namespace Docsy;

use Docsy\Exporters\AbstractExporter;
use Docsy\Exporters\JsonExporter;
use Docsy\Importers\AbstractImporter;
use Docsy\Traits\ArrayJsonSerialization;
use Docsy\Traits\HasCollections;
use Docsy\Traits\HasMeta;
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
     * @param string $formatter The Export Format, Currently Supported ["postman", "openapi.json", "openapi.yaml" and "json"]
     * @param bool $single_file Optional, if the format is set to "json" save the whole docsy to a single file or to multiple files @Default: false
     * @param string|null $save_dir The dir to save files to
     * @return void
     * @throws Exception
     */
    public function export(string $formatter, bool $single_file = true, ?string $save_dir = null) : void
    {
        $formatters = config('docsy.formatters.exporters');
        $formatter_class = $formatters[$formatter] ?? null;
        if ($formatter_class == null)
            throw new Exception("Formatter '$formatter' not found");

        if ($save_dir == null) $save_dir = rtrim(config('docsy.export_path'),'/');

        if (!$single_file) {
            mkdir($save_dir . '/Docsy_'. date('Y_m_d_h_i_s'));
            foreach ($this->collections as $collection) {
                $collection->export($formatter,$save_dir . '/Docsy_'. date('Y_m_d_h_i_s') );
            }
        } else {
            /* @var AbstractExporter $exporter */
            $exporter = new $formatter_class();

            $save_path = $save_dir . '/Docsy_'. date('Y_m_d_h_i_s') .'.' . $exporter::$export_file_ext;
            $data = $exporter::export($this);
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
    public function import(string $formatter, string $dir_or_file_path, bool $is_docsy_object_import = false) : void
    {
        $formatters = config('docsy.formatters.importers');
        $formatter = $formatters[$formatter] ?? null;
        if ($formatter == null)
            throw new Exception("Formatter '$formatter' not found");

        /* @var AbstractImporter $importer */
        $importer = new $formatter();

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

        $importer::import($is_docsy_object_import, ...$files);
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
