<?php
namespace Egor\TestBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ExpendType extends AbstractType
{
	
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$data = file('../vendor/egorzz/testbundle/test');
		$d = array();
		foreach($data as $line=>$line_num){$d[$line_num]=trim($line_num);}

        $builder
            ->add('Date', DateType::class, array('label' => 'Дата', 'widget' => 'single_text'))
		    ->add('Category', ChoiceType::class, array(
		        'label' => 'Категория', 'choices' => $d))
		    ->add('Sum', MoneyType::class, array('currency' => 'RUB', 'label' => 'Сумма'))
		    ->add('Comment', TextareaType::class, array('label' => 'Комментарий'))
		    ->add('save', SubmitType::class, array('label' => 'Создать запись'))
        ;
    }

}
