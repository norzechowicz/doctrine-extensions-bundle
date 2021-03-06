<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\DoctrineExtensionsBundle\Tests\Form\Type\FSi;

use FSi\Bundle\DoctrineExtensionsBundle\Tests\Fixtures\Entity\Article;
use FSi\Bundle\DoctrineExtensionsBundle\Tests\Fixtures\Form\Extension\FSiFileExtension;

class RemovableFileTypeTest extends FormTypeTest
{
    /**
     * @return \FSi\Bundle\DoctrineExtensionsBundle\Tests\Fixtures\Form\Extension\FSiFileExtension
     */
    public function getExtensions()
    {
        return array(
            new FSiFileExtension()
        );
    }

    public function testFormRendering()
    {
        $self = $this;

        $file = $this->getMockBuilder('FSi\DoctrineExtensions\Uploadable\File')
            ->disableOriginalConstructor()
            ->getMock();

        $file->expects($this->any())
            ->method('getFilesystem')
            ->will($this->returnCallback(function() use ($self) {
                $fileSystem = $self->getMockBuilder('FSi\Bundle\DoctrineExtensionsBundle\Listener\Uploadable\Filesystem')
                    ->disableOriginalConstructor()
                    ->getMock();

                $fileSystem->expects($self->any())
                    ->method('getBaseUrl')
                    ->will($self->returnValue('/uploaded/'));

                $fileSystem->expects($self->any())
                    ->method('getAdapter')
                    ->will($self->returnCallback(function() use ($self) {
                        $adapter = $self->getMockBuilder('Gaufrette\Adapter\Local')
                            ->disableOriginalConstructor()
                            ->getMock();

                        return $adapter;
                    }));

                return $fileSystem;
            }));

        $file->expects($this->any())
            ->method('getKey')
            ->will($this->returnValue('Article/file/1/image name.jpg'));

        $file->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Article/file/1/image name.jpg'));

        $article = new Article();
        $article->setFile($file);

        $form = $this->factory->create('form', $article);

        $form->add('file', 'fsi_removable_file');

        $html = $this->twig->render('form_with_fsi_file.html.twig', array(
            'form' => $form->createView()
        ));
        $this->assertSame($this->getExpectedHtml('form_with_fsi_removable_file.html'), $html);
    }
}
