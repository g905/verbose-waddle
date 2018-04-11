<?php

namespace Egor\TestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Money
 *
 * @ORM\Table(name="money")
 * @ORM\Entity(repositoryClass="Egor\TestBundle\Repository\MoneyRepository")
 */
class Money
{
    /**
     * @ORM\Id
     * 
     * @ORM\Column(type="integer")
     * 
     * @ORM\GeneratedValue(strategy="AUTO") 
     */
     
    protected $id;
     
	/** 
     * @ORM\Column(type="date") 
     * 
     */
    protected $date;
    
    /** 
     * @ORM\Column(type="string", length=250)
     * 
     * @Assert\NotBlank()
     */
    
    protected $category;
    
    /** 
     * @ORM\Column(type="decimal")
     * 
     * @Assert\NotBlank()
     * @Assert\Type("numeric")
     */
    
    protected $sum;
    
    /**
     * @ORM\Column(type="string", length=100)
     * 
     * @Assert\NotBlank()
     * 
     */
    
    protected $comment;

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Money
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set category
     *
     * @param string $category
     *
     * @return Money
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set sum
     *
     * @param string $sum
     *
     * @return Money
     */
    public function setSum($sum)
    {
        $this->sum = $sum;

        return $this;
    }

    /**
     * Get sum
     *
     * @return string
     */
    public function getSum()
    {
        return $this->sum;
    }

    /**
     * Set comment
     *
     * @param string $comment
     *
     * @return Money
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
    
    
    public function getReq($period = null){
		$repository = getDoctrine()
		     ->getRepository('EgorTestBundle:Money');
		     
		if(!$period) {
		    $operations = $repository->findAll();
		} elseif ($period == 'today'){
            $operations = $repository->findBy(
		        array('date' => new \DateTime('today')));
		        
		} elseif ($period == 'month'){
			
		} elseif ($period == preg_match(("/\d/g,''"))){
			$query = $repository->createQueryBuilder('p')
                         ->Where("month(p.date)*1 =".$period."")
                         ->orderBy('p.date, p.category', 'ASC')
                         ->getQuery();
                         $operations = $query->getResult();
		}
		                 
		return $operations;
	}
}
