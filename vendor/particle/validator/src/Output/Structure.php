<?php
/**
 * Particle.
 *
 * @link      http://github.com/particle-php for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Particle (http://particle-php.com)
 * @license   https://github.com/particle-php/validator/blob/master/LICENSE New BSD License
 */
namespace Particle\Validator\Output;

/**
 * Structure is an object for communicating the internal state and structure of Validator to an output object.
 *
 * @package Particle\Validator\Output
 */
class Structure
{
    /**
     * @var Subject[]
     */
    protected $subjects;

    /**
     * Add a subject (representation of Chain) to the structure.
     *
     * @param Subject $subject
     */
    public function addSubject(Subject $subject)
    {
        $this->subjects[] = $subject;
    }

    /**
     * Returns an array of all subjects.
     *
     * @return Subject[]
     */
    public function getSubjects()
    {
        return $this->subjects;
    }
}
