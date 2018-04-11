<?php
// src/FirstBundle/Entity/Category.php

/* Это класс для работы с категориями. Имеет пару свойств (Id и Name), используется только Name.
* Перенес сюда некоторую логику работы с файлом из контроллера
* Это немного разгрузило контроллер
* (метод make_array - читает построчно файл в массив и метод dump_array - перезаписывает файл)
* Ну и геттеры и сеттеры для свойств.
*/

namespace Egor\TestBundle\Entity;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class Category
{
	   
    protected $id;
     
    protected $name;
    
    public function make_array($path)
    {
		$content = file($path);
		return $content;
	}
	
	public function dump_array($array, $path, $eol=null)
	{
		try{
		    $file = new Filesystem();
		    $file->dumpFile($path, $array);
		    if ($eol) $file->appendToFile($path, PHP_EOL);
		} catch (IOExceptionInterface $exception) {
              echo "Ошибка записи ".$exception->getPath();
		  }
		return;
	}
    
    public function getId()
    {
        return $this->id;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function setName($nm)
    {
		$this->name = $nm;
        return $this->name;
    }
    
}
