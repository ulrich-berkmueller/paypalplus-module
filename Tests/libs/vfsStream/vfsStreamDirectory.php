<?php
/**
 * This file is part of OXID eSales PayPal Plus module.
 *
 * OXID eSales PayPal Plus module is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eSales PayPal Plus module is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eSales PayPal Plus module.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2015
 */

/**
 * Directory container.
 *
 * @package  bovigo_vfs
 */
/**
 * @ignore
 */
require_once dirname(__FILE__) . '/vfsStreamAbstractContent.php';
require_once dirname(__FILE__) . '/vfsStreamContainer.php';
require_once dirname(__FILE__) . '/vfsStreamContainerIterator.php';
require_once dirname(__FILE__) . '/vfsStreamException.php';
/**
 * Directory container.
 *
 * @package  bovigo_vfs
 */
class vfsStreamDirectory extends vfsStreamAbstractContent implements vfsStreamContainer
{
    /**
     * list of directory children
     *
     * @var  array<string,vfsStreamContent>
     */
    protected $children = array();

    /**
     * constructor
     *
     * @param   string  $name
     * @param   int     $permissions  optional
     * @throws  vfsStreamException
     */
    public function __construct($name, $permissions = null)
    {
        if (strstr($name, '/') !== false) {
            throw new vfsStreamException('Directory name can not contain /.');
        }

        $this->type = vfsStreamContent::TYPE_DIR;
        parent::__construct($name, $permissions);
    }

    /**
     * returns default permissions for concrete implementation
     *
     * @return  int
     * @since   0.8.0
     */
    protected function getDefaultPermissions()
    {
        return 0777;
    }

    /**
     * returns size of directory
     *
     * The size of a directory is always 0 bytes. To calculate the summarized
     * size of all children in the directory use sizeSummarized().
     *
     * @return  int
     */
    public function size()
    {
        return 0;
    }

    /**
     * returns summarized size of directory and its children
     *
     * @return  int
     */
    public function sizeSummarized()
    {
        $size = 0;
        foreach ($this->children as $child) {
            if ($child->getType() === vfsStreamContent::TYPE_DIR) {
                $size += $child->sizeSummarized();
            } else {
                $size += $child->size();
            }
        }
        
        return $size;
    }

    /**
     * renames the content
     *
     * @param   string  $newName
     * @throws  vfsStreamException
     */
    public function rename($newName)
    {
        if (strstr($newName, '/') !== false) {
            throw new vfsStreamException('Directory name can not contain /.');
        }
        
        parent::rename($newName);
    }

    /**
     * adds child to the directory
     *
     * @param  vfsStreamContent  $child
     */
    public function addChild(vfsStreamContent $child)
    {
        $this->children[$child->getName()] = $child;
        $this->updateModifications();
    }

    /**
     * removes child from the directory
     *
     * @param   string  $name
     * @return  bool
     */
    public function removeChild($name)
    {
        foreach ($this->children as $key => $child) {
            if ($child->appliesTo($name) === true) {
                unset($this->children[$key]);
                $this->updateModifications();
                return true;
            }
        }
        
        return false;
    }

    /**
     * updates internal timestamps
     */
    protected function updateModifications()
    {
        $time = time();
        $this->lastAttributeModified = $time;
        $this->lastModified          = $time;
    }

    /**
     * checks whether the container contains a child with the given name
     *
     * @param   string  $name
     * @return  bool
     */
    public function hasChild($name)
    {
        return ($this->getChild($name) !== null);
    }

    /**
     * returns the child with the given name
     *
     * @param   string  $name
     * @return  vfsStreamContent
     */
    public function getChild($name)
    {
        $childName = $this->getRealChildName($name);
        foreach ($this->children as $child) {
            if ($child->getName() === $childName) {
                return $child;
            }
            
            if ($child->appliesTo($childName) === true && $child->hasChild($childName) === true) {
                return $child->getChild($childName);
            }
        }
        
        return null;
    }

    /**
     * helper method to detect the real child name
     *
     * @param   string  $name
     * @return  string
     */
    protected function getRealChildName($name)
    {
        if ($this->appliesTo($name) === true) {
            return self::getChildName($name, $this->name);
        }
        
        return $name;
    }

    /**
     * helper method to calculate the child name
     *
     * @param   string  $name
     * @param   string  $ownName
     * @return  string
     */
    protected static function getChildName($name, $ownName)
    {
        return substr($name, strlen($ownName) + 1);
    }

    /**
     * checks whether directory contains any children
     *
     * @return  bool
     * @since   0.10.0
     */
    public function hasChildren()
    {
        return (count($this->children) > 0);
    }

    /**
     * returns a list of children for this directory
     *
     * @return  array<vfsStreamContent>
     */
    public function getChildren()
    {
        return array_values($this->children);
    }

    /**
     * returns iterator for the children
     *
     * @return  vfsStreamContainerIterator
     */
    public function getIterator()
    {
        return new vfsStreamContainerIterator($this->children);
    }
}
?>