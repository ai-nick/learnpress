<?php

/**
 * Class LP_Question_Answers
 *
 * @since 3.x.x
 */
class LP_Question_Answers implements ArrayAccess, Iterator {
	/**
	 * Answers
	 *
	 * @var array
	 */
	protected $_answers = array();

	/**
	 * Current position of answers.
	 *
	 * @var int
	 */
	protected $_position = 0;

	/**
	 * @var bool
	 */
	protected $_randomize_options = false;

	/**
	 * Original positions of answers (maybe useful later).
	 *
	 * @var bool
	 */
	protected $_origin_positions = false;

	/**
	 * @var LP_Question
	 */
	protected $_question = null;

	/**
	 * LP_Question_Answers constructor.
	 *
	 * @param $raw
	 */
	public function __construct( $question, $raw ) {
		$this->_question = $question;
		$this->_init( $raw );
	}

	/**
	 * Set option randomize answer options.
	 *
	 * @param bool $randomize
	 */
	public function set_randomize_options( $randomize ) {
		$this->_randomize_options = (bool) $randomize;
	}

	/**
	 * @return bool
	 */
	public function get_randomize_options() {
		return $this->_randomize_options;
	}

	/**
	 * Init answer options.
	 *
	 * @param array $raw
	 */
	protected function _init( $raw ) {
		if ( ! $raw ) {
			return;
		}

		foreach ( $raw as $data ) {
			$key                    = $data['question_answer_id'];
			$answer                 = new LP_Question_Answer_Option( $this->_question, $data );
			$this->_answers[ $key ] = $answer;
		}

		// Keep origin positions
		$this->_origin_positions = array_keys( $this->_answers );

		// shuffle
		if ( $this->_randomize_options ) {
			$this->_shuffle();
		}
	}

	public function get_question() {
		return $this->_question;
	}

	public function get_question_id() {
		return $this->_question->get_id();
	}

	/**
	 * Is exists an answer option with passed offset?
	 *
	 * @param string|int $offset
	 *
	 * @return bool
	 */
	public function offsetExists( $offset ) {
		return array_key_exists( $offset, $this->_answers );
	}

	/**
	 * Get answer option by offset.
	 *
	 * @param mixed $offset
	 *
	 * @return bool|mixed
	 */
	public function offsetGet( $offset ) {
		return $this->offsetExists( $offset ) ? $this->_answers[ $offset ] : false;
	}

	/**
	 * Set answer option by offset.
	 *
	 * @param int|string $offset
	 * @param mixed      $value
	 */
	public function offsetSet( $offset, $value ) {
		$this->_answers[ $offset ] = $value;
	}

	/**
	 * Unset answer option by offset.
	 *
	 * @param int|string $offset
	 */
	public function offsetUnset( $offset ) {
		if ( $this->offsetExists( $offset ) ) {
			unset( $this->_answers[ $offset ] );
		}
	}

	/**
	 * Reset current position of answer options.
	 */
	public function rewind() {
		$this->_position = 0;
		if ( $this->_randomize_options ) {
			$this->_shuffle();
		}
	}

	/**
	 * @return mixed
	 */
	public function current() {
		$values = array_values( $this->_answers );

		return $values[ $this->_position ];
	}

	/**
	 * @return mixed
	 */
	public function key() {
		$keys = array_keys( $this->_answers );

		return $keys[ $this->_position ];// $this->_position;
	}

	/**
	 *
	 */
	public function next() {
		++ $this->_position;
	}

	/**
	 * @return bool
	 */
	public function valid() {
		$values = array_values( $this->_answers );

		return isset( $values[ $this->_position ] );
	}

	/**
	 * Shuffle options
	 */
	protected function _shuffle() {
		LP_Helper::shuffle_assoc( $this->_answers );
	}

	public function get_class( $more = '' ) {
		$classes = array( 'answer-options' );
		if ( $more && is_string( $more ) ) {
			$more = explode( ' ', $more );
		}

		if ( $more && is_array( $more ) ) {
			$classes = array_merge( $classes, $more );
		}
		if ( $this->get_question()->show_correct_answers() === 'yes' ) {
			$classes[] = 'disabled';
		}

		// sanitize unwanted classes
		$classes = LP_Helper::sanitize_array( $classes );

		return apply_filters( 'learn-press/question/answer-option/classes', $classes, $this );
	}

	public function answers_class( $more = '' ) {
		$classes = $this->get_class( $more );
		echo 'class="' . join( ' ', $classes ) . '"';
	}
}

/**
 * Class LP_Question_Answer_Option
 *
 * @since 3.x.x
 */
class LP_Question_Answer_Option implements ArrayAccess {
	/**
	 * Option data
	 *
	 * @var array
	 */
	protected $_data = null;

	/**
	 * @var LP_Question
	 */
	protected $_question = null;

	/**
	 * LP_Question_Answer_Option constructor.
	 *
	 * @param LP_Question $question
	 * @param mixed       $data
	 */
	public function __construct( $question, $data ) {
		$this->_data     = $data;
		$this->_question = $question;
	}

	/**
	 * Get option title.
	 *
	 * @return string
	 */
	public function get_title() {
		$title = array_key_exists( 'text', $this->_data ) ? $this->_data['text'] : '';

		return apply_filters( 'learn-press/question/option-title', $title, $this );
	}

	/**
	 * Get option value.
	 *
	 * @return string
	 */
	public function get_value() {
		$value = array_key_exists( 'value', $this->_data ) ? $this->_data['value'] : '';

		return apply_filters( 'learn-press/question/option-value', $value, $this );
	}

	/**
	 * Return true if option is TRUE
	 * @return bool
	 */
	public function is_true() {
		return array_key_exists( 'is_true', $this->_data ) && $this->_data['is_true'] === 'yes';
	}

	/**
	 * CSS class for option
	 *
	 * @param string $more
	 *
	 * @return array
	 */
	public function get_class( $more = '' ) {
		$classes = array( 'answer-option' );
		if ( $more && is_string( $more ) ) {
			$more = explode( ' ', $more );
		}

		if ( $more && is_array( $more ) ) {
			$classes = array_merge( $classes, $more );
		}
		if ( $this->get_question()->show_correct_answers() === 'yes' ) {
			if ( $this->is_true() ) {
				$classes[] = 'answer-correct';
			}
			if ( $this->is_checked() && $this->is_true() ) {
				$classes[] = 'answered-correct';
			} elseif ( $this->is_checked() && ! $this->is_true() ) {
				$classes[] = 'answered-wrong';
			}
		}

		// sanitize unwanted classes
		$classes = LP_Helper::sanitize_array( $classes );

		return apply_filters( 'learn-press/question/answer-option/classes', $classes, $this );
	}

	public function checked( $echo = true ) {

		return checked( $this->is_checked(), true, $echo );
	}

	public function disabled( $echo = true ) {
		$q        = $this->_question;
		$disabled = ( $q->show_correct_answers() === 'yes' ) || ( $q->disable_answers() === 'yes' );

		return disabled( $disabled, true, $echo );
	}

	public function get_answered() {
		return $this->get_question()->get_answered();
	}

	public function is_checked() {
		if ( false === $this->get_answered() ) {
			return false;
		}

		return in_array( $this->get_value(), (array) $this->get_answered() );
	}


	/**
	 * @return bool|LP_Question
	 */
	public function get_question() {
		return $this->_question;
	}

	public function get_question_id() {
		return $this->_question->get_id();
	}

	/**
	 * Print class attribute
	 *
	 * @param string $more
	 */
	public function option_class( $more = '' ) {
		echo 'class="' . join( ' ', $this->get_class( $more ) ) . '"';
	}

	/**
	 * Backward compatibility for accessing property as array.
	 *
	 * @param mixed $offset
	 *
	 * @return bool
	 */
	public function offsetExists( $offset ) {
		return array_key_exists( $offset, $this->_data );
	}

	/**
	 * Backward compatibility for get property as array.
	 *
	 * @param mixed $offset
	 *
	 * @return mixed
	 */
	public function offsetGet( $offset ) {
		return $this->offsetExists( $offset ) ? $this->_data[ $offset ] : false;
	}

	/**
	 * Backward compatibility for set property as array.
	 *
	 * @param mixed $offset
	 */
	public function offsetSet( $offset, $value ) {
		$this->_data[ $offset ] = $value;
	}

	/**
	 * Backward compatibility for unset property as array.
	 *
	 * @param mixed $offset
	 */
	public function offsetUnset( $offset ) {
		if ( $this->offsetExists( $offset ) ) {
			unset( $this->_data[ $offset ] );
		}
	}
}