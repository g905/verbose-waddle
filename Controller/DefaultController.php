<?php

namespace Egor\TestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Egor\TestBundle\Entity\Money;
use Egor\TestBundle\Entity\Category;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Egor\TestBundle\Form\ExpendType;
use DoctrineExtensions\Query\Sqlite\Month;
use DoctrineExtensions\Query\Sqlite\Day;
use Doctrine\Common\Collections;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('@EgorTest/Default/index.html.twig');
    }

    public function show_allAction(Request $request)
	 {
		 $operations = array();
		 $days = array();
		 $cats = array();
		 $cat_summary = array();
		 $sums = 0;
		 $template = '@EgorTest/Default/base.html.twig';
		 $period = $request->get('period');
		 $conf = file($this->container->getParameter('egor_test.limits_'));
		 
		 foreach($conf as $value){
			  $confs[] = explode ("=", $value);
		 }
         
		 $limit = (real)$confs[1][1];
		 $scenario = trim($confs[0][1]);
		 $diff = (real)($limit-$confs[2][1]);
         
         //echo gettype($diff);
		 
		 //Получаем объект из БД со всеми записями
		 $repository = $this->getDoctrine()
		     ->getRepository('EgorTestBundle:Money');
 
         $m = new \DateTime('today');
         $m = $m->format('m');

         $c = $repository->createQueryBuilder('p')
                         ->where("month(p.date)*1 = ".$m)
                         ->getQuery();
             
             $quer = $c->getResult();
             $current_sum = 0;
             foreach($quer as $key => $val) {
				 $current_sum += $val->getSum();
			 }

         if($current_sum > $limit){
			 $extra = ($current_sum-$limit);
			 if($scenario == 'adaptive'){
				 if($diff == $extra){$alert = false;} else {$alert = true;}
				 $data__ = 'scenario='.$scenario.PHP_EOL.'current_month_limit='.$limit.PHP_EOL.'next_month_limit='.($limit-$extra);
                 

			 } elseif($scenario == 'extending'){
				 $data__ = 'scenario='.$scenario.PHP_EOL.'current_month_limit='.($limit+$extra).PHP_EOL.'next_month_limit='.($limit+$extra);
				 $alert = true;
			 }
			 try{
				 $file = new Filesystem;
			     $file->dumpFile($this->container->getParameter('egor_test.limits_'), $data__);
			 } catch(IOExceptionInterface $exception) {
              echo "Ошибка записи ".$exception->getPath();
			 } 
		 } else {
			 $extra = null;
			 $alert = false;
		 }
 
		     //Если не указан период, то берем все записи из БД
		     if(!$period) {
				 $operations = $repository->findAll();
 
				 //Если указан период - сегодня, выбираем записи только за сегодня
		     } elseif ($period == 'today'){
                     $operations = $repository->findBy(
		                 array('date' => new \DateTime('today')));
                 
		         //Если указан период месяц,        
		       } elseif ($period == 'month') {
				   
				   //Выбираем существующие в БД месяцы и суммы
				   $query = $repository->createQueryBuilder('p')
                         ->select("month(p.date)*1 as month, p.sum")
                         ->orderBy('month(p.date)', 'ASC')
                         ->getQuery();
     
                         $operations = $repository->findAll();
                         
                         $unique_months = $query->getResult();
                         //Считаем суммы по каждому месяцу
					     $month_sum = 0;
					     $sums = array();
					     for($i = 0; $i<=count($unique_months)-1; ++$i) {
							 $current = current($unique_months);
							 $next = next($unique_months);
								 if ($current['month'] == $next['month']) {
									 $month_sum += $current['sum']; 

						     } else {
								   
								 $month_sum += $current['sum'];
								 $sums += [$current['month'] => $month_sum];
								 $month_sum = 0;
								   }
						 }
						 
						 //return new Response('POSSIBLE');
						 
			   } else {
				   
				   $per = $period*1;
                     //Если указан период в виде цифры месяца, то выбираем записи за данный месяц
                     $query = $repository->createQueryBuilder('p')
                         ->Where("month(p.date)*1 =".$period)
                         ->orderBy('p.date, p.category', 'ASC')
                         ->getQuery();
                         $operations = $query->getResult();
                     
                         foreach($operations as $k => $value ){
							 array_push($cats, $value->getCategory());
						 }
						 $cats = array_unique($cats);
						 $cat_summary = array_fill_keys($cats, 0);
						 foreach($cats as $value){
							 foreach($operations as $k => $v){
								 if($v->getCategory() === $value){
									 $cat_summary[$value] = $cat_summary[$value]+$v->getSum();
								 }
							 }
						 }
                         
                         $sums = 0;
                         $sums_d = 0;
                         $days = [];
                         
                         //Собираем двухмерный массив для записи сумм по каждой категории. Что-то типа "День1:Категория1:Сумма; День1:Категория2:Сумма"
                         //Сначала создадим $days, отберем только уникальные дни месяца, пока только ключи, без значений
                         for($i = 0; $i < count($operations)-1; $i++){
							 if ($operations[$i]->getDate()->format('d') != $operations[$i+1]->getDate()->format('d')){
								 $days[$operations[$i]->getDate()->format('d')] = [];
							 } 
							 elseif ($operations[$i]->getDate()->format('d') != end($operations)->getDate()->format('d')){
								 $days[$operations[$i]->getDate()->format('d')] = [];
							 }
							 
						 }
						 
						 //Костыль для месяцев, где всего одна запись
						 if(count($operations) == 1){
								 $days[$operations[0]->getDate()->format('d')] = [];
							 }
							 
						 //Далее среди уникальных дней отберем уникальные категории, пока поставим 'sum' в качестве итоговой суммы
						 for($i = 0; $i < count($operations)-1; $i++){
								 if($operations[$i]->getCategory() != $operations[$i+1]->getCategory()){
									 $days[$operations[$i]->getDate()->format('d')][$operations[$i]->getCategory()] = 'sum' ;
								 } elseif($operations[$i]->getCategory() != end($operations)->getCategory()) {
									 $days[end($operations)->getDate()->format('d')][end($operations)->getCategory()] = 'sum' ;
								 }
						 }
						 //Костыль
						 if(count($operations) == 1){
								 $days[$operations[0]->getDate()->format('d')][$operations[0]->getCategory()] = 'sum' ;
							 }
						 
						 //Наконец для каждого уникального дня, соберем итоговую сумму по каждой категории.
						 for($i = 0; $i < count($operations)-1; $i++){
							 
							 if ($operations[$i]->getDate()->format('d') == $operations[$i+1]->getDate()->format('d') && $operations[$i]->getCategory() == $operations[$i+1]->getCategory()){
								 $sums_d += $operations[$i]->getSum();
							 } else {
								 $sums_d += $operations[$i]->getSum();
								 $days[$operations[$i]->getDate()->format('d')][$operations[$i]->getCategory()] = $sums_d;
								 $sums_d = 0;
							 }
							 
							 if($operations[$i+1]->getDate()->format('d') == end($operations)->getDate()->format('d') && $operations[$i+1]->getCategory() == end($operations)->getCategory()){
							     $sums_d += end($operations)->getSum();
							     $days[end($operations)->getDate()->format('d')][end($operations)->getCategory()] = $sums_d;
							 }

						 }
						 
						 //Костыль
						 if(count($operations) == 1){
								 $days[$operations[0]->getDate()->format('d')][$operations[0]->getCategory()] = $operations[0]->getSum();
							 }
		     }
		     
		 //Если не удалось получить ни одной записи
		 if(!$operations){
			 throw $this->createNotFoundException('Записей не найдено');
		 }
		 
		 //Выводим все записи в таблицу через паджинатор
		 $paginator  = $this->get('knp_paginator');
         $pagination = $paginator->paginate($operations, $request->query->getInt('page', 1), 10);
		 
		 //Делаем форму. Создаем расход, дата по умолчанию сегодня
		 $operation = new Money();
		 $operation->setDate(new \DateTime("today"));
		 
		 //Создаем форму из ее класса
		 $form = $this->createForm(ExpendType::class, $operation);
		     
		 $form->handleRequest($request);

		 //После сабмита валидируем и записываем в БД через Доктрине
		 if ($form->isSubmitted() && $form->isValid()){
			 $operations = $form->getData();
			 
			 $entityManager = $this->getDoctrine()->getManager();
			 $entityManager->persist($operation);
			 $entityManager->flush();
			 
			 //Возвращаемся к текущему маршруту
			 return $this->redirect($request->getUri());
		 }
		 
		 //Считаем суммы за выбранный период
		 $s = array();
		 //Неудобно работать с массивом объектов/массивов "operations", создаем одномерный массив и записываем в него все суммы
		 for($i=0; $i<count($operations); $i++) {
			 if(gettype($operations[$i]) == 'object'){
			     array_push($s, $operations[$i]->getSum());
		     } else {
			     array_push($s, $operations[$i]['sum']);
		     }
		 }
		 //А потом просто складываем их все
		 $sum = array_sum($s);
		 
		 //Собираем массив количества дней во всех месяцах года
		 for($i = 1; $i<=12; $i++){
			 $qdays[] = cal_days_in_month(CAL_GREGORIAN, $i, 2018);
		 }
		 
		 //Если не было сабмита формы, то выводим список расходов и пустую форму. Передаем все необходимые параметры.
		 return $this->render($template, array(
            'form' => $form->createView(),
            'pagination' => $pagination, //Паджинатор
            'sum' => $sum, //Сумма за текущий период
            'limit' => $limit, //Месячный лимит
            'sums' => $sums, //Суммы по каждому месяцу
            'days' => $days, //Дни, в которые были расходы
            'period' => $period, //Период: записи за день, месяц, или все подряд
            'cats' => $cats, //Категории из БД
            'cat_summary' => $cat_summary, //Массив с суммами по каждой категории
            'qdays' => $qdays, //Количество дней в месяцах (30, 31, 28, 29)
            'scenario' => $scenario, //Какой сценарий применять после превышения лимита?
            'alert' => $alert,//Выводим или не выводим предупреждение о превышении (труе/фальсе)
            'extra' => $extra//Величина превышения
        ));
	 }
     
     //Экшен редактирования категорий расходов
	 public function categoriesAction(Request $request)
	 {
         //Создаем объект из отдельного класса
         $category = new Category();
         //Используем собственный метод. Читаем файл со списком категорий и помещаем каждую строку в массив.
         $categories = $category->make_array('../src/Egor/TestBundle/test');
         //Для этой формы нет отдельного класса. Она же маленькая.
         $form = $this->createFormBuilder($categories)
             ->add('name', TextType::class, array('label' => 'Название'))
             ->add('save', SubmitType::class, array('label' => 'Добавить'))
             ->getForm();
         $form->handleRequest($request);
         if ($form->isSubmitted() && $form->isValid()){
			 $data = $form->getData();
			 //Используем собственный метод. После сабмита формы открываем файл, производим манипуляции с массивом и перезаписываем массив в файл.
			 $category->dump_array($data, $this->container->getParameter('egor_test.categories_'), true);
			 //Возвращаемся к категориям.
	         return $this->redirectToRoute('egor_test_cat_show');
		 }
		 //Если не сабмитили форму, то выводим категории и рисуем форму.  
		 return $this->render('@EgorTest/Default/base.html.twig', array(
		     'cat_form' => $form->createView(),
		     'cat' => $categories,
		 ));
	 }
	 
	 //Экшен удаления категории
	 public function del_categoryAction($id){
		 
		 //Создаем собственный объект
		 $categories = new Category();
		 
		 //Получаем массив из файла с категориями, используя собственный метод
		 $content = $categories->make_array($this->container->getParameter('egor_test.categories_'));
		 
		 //Удаляем элемент с Ид, полученным из маршрута
		 unset($content[$id-1]);
		 
		 //Переиндексируем массив
		 $content = array_values($content);
		 
		 //Перезаписываем массив в файл категорий
		 $categories->dump_array($content, $this->container->getParameter('egor_test.categories_'));
		 
		 //Идем обратно
		 return $this->redirectToRoute('egor_test_cat_show');
	 }

    //Настройка лимитов
    public function limitsAction(Request $request, $extra=null){
		//Берем файл настроек
		$conf = file($this->container->getParameter('egor_test.limits_'));
		//Создаем массив из файла
		foreach($conf as $value){
			 $confs[] = explode ("=", $value);
		 }
		 //Вместо создания объекта, создаем простой массив для формы
		 $default = array('scenario' => 'adaptive', 'month_limit' => '30000');
		//Создаем форму
		$limit_form = $this->createFormBuilder($default)
             ->add('scenario', ChoiceType::class, array('label' => 'Сценарий', 'choices' => ['Адаптивный' => 'adaptive', 'Расширяемый' => 'extending']))
             ->add('limit', MoneyType::class, array('currency' => 'RUB', 'label' => 'Лимит', 'data' => (real)$confs[1][1]))
             ->add('save', SubmitType::class, array('label' => 'Сохранить'))
             ->getForm();
             
         $limit_form->handleRequest($request);
         
         if ($limit_form->isSubmitted() && $limit_form->isValid()){
             //Собираем контент для файла
			 $data = $limit_form->getData();
			 $data_ = 'scenario='.$data['scenario'].PHP_EOL.'current_month_limit='.$data['limit'].PHP_EOL.'next_month_limit='.$confs[2][1];
			 try{
				 $file = new Filesystem;
			     $file->dumpFile($this->container->getParameter('egor_test.limits_'), $data_);

			 } catch(IOExceptionInterface $exception) {
                 echo "Ошибка записи ".$exception->getPath();
			 }
	         return $this->redirectToRoute('egor_test_db_limits');
		 }
				
		return $this->render('@EgorTest/Default/base.html.twig', array(
		     'limits' => (real)$confs[1][1], 
		     'scenario' => trim($confs[0][1]),
		     'limit_form' => $limit_form->createView()
		 ));
	}
     
}
