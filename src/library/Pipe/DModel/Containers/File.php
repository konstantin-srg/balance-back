<?php
namespace Pipe\DModel\Containers;

use Pipe\Db\Entity\Containers\AbstractContainer;

class File extends AbstractContainer
{
    /** @var \Pipe\File\File */
    protected $file;
    protected $newFile = [];

    public function get($options = null)
    {
        return $this->file()->setFileName($this->getSource());
    }

    public function remove()
    {
        $this->file()->remove();
        $this->setSource('');
    }

    public function set($value)
    {
        if(!$value) return $this;

        if(is_array($value)) {
            $newFileName = $this->file()->getUniqueFilename($this->file()->getExtension($value['name']));

            $this->file()->setFileName($this->getSource());

            $this->newFile = [
                'fileName'  => $newFileName,
                'file'      => $value['tmp_name'],
            ];

            $this->setSource($newFileName);
        }
        return $this;
    }

    public function save()
    {
        if(!$this->newFile) return true;

        $this->file()
            ->remove()
            ->setFileName($this->newFile['fileName'])
            ->save($this->newFile['file']);

        return true;
    }

    protected function file()
    {
        if(!$this->file) {
            $file = new \Pipe\File\File();

            if(!$this->getOptions()['path']) {
                throw new \Exception('Unknown dir for saving files');
            }

            $file->setBasePath($this->getOptions()['path']);

            $this->file = $file;
        }
        return $this->file;
    }
}