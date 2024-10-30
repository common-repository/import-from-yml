<?php
/**
 * Starts feed import
 *
 * @package                 Import from YML
 * @subpackage              
 * @since                   3.1.0
 * 
 * @version                 3.1.3 (28-03-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     https://www.php.net/manual/ru/class.xmlreader.php
 * 
 * @param        
 *
 * @depends                 classes:    XMLReader
 *                                      SimpleXMLElement
 *                          traits:     
 *                          methods:    
 *                          functions:  
 *                          constants:  
 *                          options:    
 */
defined( 'ABSPATH' ) || exit;

class IPYTW_XML_Parsing {
	/**
	 * Путь к XML файлу
	 * @var string
	 */
	var $xml_file;
	/**
	 * XML Reader object
	 * @var XMLReader
	 */
	var $reader;
	/**
	 * Iteration of main loop
	 * @var int
	 */
	var $iteration;

	/**
	 * Summary of __construct
	 * 
	 * @param string $xml_file - Required
	 * 
	 */
	public function __construct( $xml_file ) {
		$this->xml_file = $xml_file;
		$this->reader = new XMLReader();
		$this->reader->open( $xml_file );
	}

	/**
	 * Устанавливает указатель основного цикла на необходимый элемент в фиде с учётом его позиции в фиде
	 * Нумерация начинатеся 1
	 * 
	 * @param int $iteration - Required
	 * @param string $element_name - Required
	 * 
	 * @return bool - `false` - если нет такого элемента по счёту, `true` - если есть
	 */
	public function set_pointer_to( $iteration, $element_name ) {
		if ( $this->element_count( $element_name ) < $iteration ) {
			return false;
		}
		$i = 1;
		$this->reader = new XMLReader();
		$this->reader->open( $this->get_xml_file() );
		while ( $this->reader->read() ) {
			if ( $this->reader->nodeType == XMLReader::ELEMENT ) {
				if ( $this->reader->localName == $element_name ) {
					if ( $iteration == $i ) {
						break;
					}
					$i++;
				}
			}
		}
		$this->iteration = $iteration;
		return true;
	}

	/**
	 * Возвращает количество элементов offer или тех, что переданы в первом параметре. С этой функцией мы также можем
	 * искать максимально заданное число элементов и останавливать поиск, при достижении этого числа
	 * 
	 * @param string $element_name - Required
	 * @param int $max_count_element - Optional (min value = 1)
	 * 
	 * @return int
	 */
	public function element_count( $element_name, $max_count_element = 999999 ) {
		$i = 0;
		$reader = new XMLReader();
		$reader->open( $this->get_xml_file() );
		while ( $reader->read() ) {
			if ( $reader->nodeType == XMLReader::ELEMENT ) {
				if ( $reader->localName == $element_name ) {
					$i++;
				}
			}
			if ( $i === $max_count_element ) {
				break;
			}
		}
		return $i;
	}

	/**
	 * Возвращает XML из текущего узла, включая сам узел и преобразует узел в объек SimpleXML.
	 * Данные берёт либо из основного цикла класса, либо из параметра, который передан функции.
	 * 
	 * @param XMLReader $reader - Optional
	 * 
	 * @return SimpleXMLElement
	 */
	public function get_outer_xml( $reader = null ) {
		if ( $reader ) {
			$xml_string = $reader->readOuterXml();
		} else {
			$xml_string = $this->get_reader()->readOuterXml();
		}
		$xml_object = new SimpleXMLElement( $xml_string );
		return $xml_object;
	}

	/**
	 * Возвращает значение group_id, если таковое имеется
	 * 
	 * @param XMLReader $reader - Required
	 * 
	 * @return string
	 */
	public function get_group_id( $reader ) {
		if ( null == $reader->getAttribute( 'group_id' ) ) {
			$group_id = '';
		} else {
			$group_id = $reader->getAttribute( 'group_id' );
		}
		return $group_id;
	}

	/**
	 * Get XMLReader
	 * 
	 * @return XMLReader
	 */
	public function get_reader() {
		return $this->reader;
	}

	/**
	 * Get iteration of main loop
	 * 
	 * @return int
	 */
	public function get_iteration() {
		return $this->iteration;
	}

	/**
	 * Get XML file path
	 * 
	 * @return string
	 */
	public function get_xml_file() {
		return $this->xml_file;
	}
}