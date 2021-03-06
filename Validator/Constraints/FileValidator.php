<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\DoctrineExtensionsBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\FileValidator as BaseValidator;
use FSi\DoctrineExtensions\Uploadable\File as FSiFile;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ExecutionContextInterface;

class FileValidator extends ConstraintValidator
{
    /**
     * @var \Symfony\Component\Validator\Constraints\FileValidator
     */
    private $symfonyValidator;

    public function __construct(BaseValidator $symfonyValidator)
    {
        $this->symfonyValidator = $symfonyValidator;
    }

    /**
     * @param \Symfony\Component\Validator\ExecutionContextInterface $context
     */
    public function initialize(ExecutionContextInterface $context)
    {
        parent::initialize($context);

        $this->symfonyValidator->initialize($context);
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value instanceof FSiFile) {
            $tmpFile = sys_get_temp_dir() . '/' . uniqid();
            file_put_contents($tmpFile, $value->getContent());

            try {
                $this->symfonyValidator->validate($tmpFile, $constraint);
            } catch (\Exception $e) {
                unlink($tmpFile);
                throw $e;
            }

            unlink($tmpFile);
            return;
        }

        $this->symfonyValidator->validate($value, $constraint);
    }
}
