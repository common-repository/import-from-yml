<?php
/**
 * Sorting categories before importing
 *
 * @package                 Import from YML
 * @subpackage              
 * @since                   3.1.2
 * 
 * @version                 3.1.5 (29-08-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see           
 * 
 * @param     array         $original_arr
 *
 * @depends                 classes:    IPYTW_Error_Log
 *                          traits:     
 *                          methods:    
 *                          functions:  
 *                          constants:  
 *                          options:    
 */
defined( 'ABSPATH' ) || exit;

class IPYTW_Sorting_Categories {
	/**
	 * Оригинальный массив с хаотичным порядком категорий. Каждый элемент массива - объект:
	 * [0] => (object)
	 * ---[id] => (integer)1005
	 * ---[parent_id] => (integer)1004
	 * ---[name] => (string)Столики туалетные
	 * [1] => (object)
	 * ---[id] => (integer)1004
	 * ...
	 * @var array
	 */
	private $original_arr = [];
	/**
	 * Новый массив с правильным порядком элементов. Каждый элемент массива - объект:
	 * [0] => (object)
	 * ---[id] => (integer)1004
	 * ---[parent_id] => (integer)1003
	 * ---[name] => (string)Столы
	 * 
	 * [1] => (object)
	 * ---[id] => (integer)1005
	 * ---[parent_id] => (integer)1004
	 * ...
	 * @var array
	 */
	private $new_arr = [];
	/**
	 * Массив с ID уже обработанных категорий
	 * @var array
	 */
	private $already_imported_arr = [];

	/**
	 * Sorting categories before importing
	 * 
	 * @param array $original_arr
	 */
	public function __construct( $original_arr ) {
		$this->original_arr = $original_arr;
		$this->do_new_arr();
	}

	/**
	 * Функция запускает создание нового массива
	 * 
	 * @return void
	 */
	public function do_new_arr() {
		new IPYTW_Error_Log( sprintf(
			'Стартовала сортировка категорий перед импортом; Файл: %1$s; Строка: %2$s',
			'class-ipytw-sorting-categories.php',
			__LINE__
		) );
		for ( $i = 0; $i < count( $this->get_original_arr() ); $i++ ) {
			$this->making_decision( $this->get_original_arr()[ $i ], $i );
		}
	}

	/**
	 * Функция определяет, как пуступить с очередной категорией
	 * 
	 * @param array $original_arr
	 * @param int $i
	 * 
	 * @return void
	 */
	private function making_decision( $category_info_arr, $i ) {
		if ( true == $this->is_already_imported( $category_info_arr['id'] ) ) {
			// если эта категория уже импортирована в новый массив, то ничего не делаем
		} else if ( 0 == $category_info_arr['parent_id'] ) {
			// если это родительская категория, то добавляем в список
			$this->add_this_to_new_arr( $category_info_arr, 'start' );
		} else {
			// если это НЕ родительскя категория и она НЕ импортирована в новый массив
			// то проверим, есть ли её категория-родитель в оставшейся части списка
			$index = $this->is_there_one( $category_info_arr['parent_id'] );
			if ( -1 === $index ) {
				new IPYTW_Error_Log( sprintf(
					'NOTICE: Категория c ID = %1$s имеет битого родителя. Делаем её родительской; Файл: %2$s; Строка: %3$s',
					$category_info_arr['id'],
					'class-ipytw-sorting-categories.php',
					__LINE__
				) );
				// категории родителя в оставшейся части нет. Добавляем данную категорию как родительскую
				$arr = [ 
					'id' => (int) $category_info_arr['id'],
					'parent_id' => (int) 0,
					'name' => $category_info_arr['name']
				];
				$this->add_this_to_new_arr( $arr, 'start' );
			} else {
				// категория родитель есть. примем решение относительно неё
				if ( false == $this->is_already_imported( $category_info_arr['id'] ) ) {
					array_push( $this->already_imported_arr, $category_info_arr['id'] );
					$this->making_decision( $this->get_original_arr()[ $index ], $i );
					array_push( $this->new_arr, $category_info_arr );
					// $this->add_this_to_new_arr( $category_info_arr, 'end' );
				}
			}
		}
	}

	/**
	 * Функция определяет, есть ли в оставшейся, не обработанной части оригинального массива категория с указанным ID
	 * 
	 * @param int $search_id
	 * 
	 * @return int
	 */
	private function is_there_one( $search_id ) {
		$index = -1;
		for ( $n = 0; $n < count( $this->get_original_arr() ); $n++ ) {
			if ( $search_id == $this->get_original_arr()[ $n ]['id'] ) {
				new IPYTW_Error_Log( sprintf( '$search_id = %s найден в элементе оригинального массива original_arr[%s] = %s; Файл: %s; Строка: %s',
					$search_id,
					$n,
					$this->get_original_arr()[ $n ]['id'],
					'class-ipytw-sorting-categories.php',
					__LINE__
				) );
				return $n; // есть такая категория в списке, возвращаем её индекс
			}
		}
		return $index;
	}

	/**
	 * Функция определяет, добавлена ли в список данная категория или ещё нет
	 * 
	 * @param int $category_id
	 * 
	 * @return bool
	 */
	private function is_already_imported( $category_id ) {
		if ( in_array( $category_id, $this->get_already_imported_arr() ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Функция добавляет в список новую категорию
	 * 
	 * @param array $arr - Required
	 * @param string $to - Optional
	 * 
	 * @return void
	 */
	private function add_this_to_new_arr( $arr, $to = 'end' ) {
		if ( $to === 'end' ) {
			array_push( $this->already_imported_arr, $arr['id'] );
			array_push( $this->new_arr, $arr );
		} else {
			array_unshift( $this->already_imported_arr, $arr['id'] );
			array_unshift( $this->new_arr, $arr );
		}
	}

	/**
	 * Возвращает оригинальный массив категорий
	 * 
	 * @return array
	 */
	private function get_original_arr() {
		return $this->original_arr;
	}

	/**
	 * Возвращает массив категорий после обработки
	 * 
	 * @return array
	 */
	public function get_new_arr() {
		return $this->new_arr;
	}

	/**
	 * Возвращает массив с id обработанных категорий
	 * 
	 * @return array
	 */
	private function get_already_imported_arr() {
		return $this->already_imported_arr;
	}
}