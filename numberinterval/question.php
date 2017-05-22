<?php

/**
 * Numberinterval question definition class.
 *
 * @package    qtype
 * @subpackage numberinterval
 * @copyright  2017 Uldis Dzilna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/questionbase.php');

/**
 * Represents a numberinterval question.
 *
 * @copyright  2017 Uldis Dzilna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_numberinterval_question extends question_graded_automatically {
	public $choices;
	
	public $rightchoices;
	
	public $intervalcount;
	//public $rightvalues;
	
	public function get_expected_data() {
		$vars = array();
		for ($i=1;$i<=$this->intervalcount*2;$i++) {
			$vars[$this->field($i)] = PARAM_INTEGER;
        }
        return $vars;
	}
	
	public function field($index) {
		return "a_".$index;
	}
	
	public function get_correct_response() {
		$response = array();
        foreach ($this->rightchoices as $index => $answer) {
            $response[$this->field($index)] = $answer;
        }
        return $response;
	}
	
	public function is_complete_response(array $response) {
            $odd = true;
            $complete = true;
            for($i=1;$i<=$this->intervalcount*2;$i++) {
                if ($odd) {
                    if(empty($response[$this->field($i)])) {
                        $emptyOne = true;
                    } else {
                        $emptyOne = false;
                    }
                    $odd = false;
                } else {
                    if(empty($response[$this->field($i)])) {
                        $emptyTwo = true;
                    } else {
                        $emptyTwo = false;
                    }
                    // for each interval (a pair of values) we check if they are different 
                    // (one filled, other empty)
                    $different = ($emptyOne ^ $emptyTwo);
                    // if they are different, response is not complete
                    if ($different) {
                        $complete = false;
                    } else {
                        // special case - the first interval should always be filled
                        // it could be they are not different, but both empty
                        // therefore we check whether that is the case
                        if ($i==2 && $emptyTwo) {
                            $complete = false;
                        }
                    }
                    $odd = true;
                }
            }
            return $complete;
	}
	
	public function is_same_response(array $prevresponse, array $newresponse) {
        if (!question_utils::arrays_same_at_key_missing_is_blank(
                $prevresponse, $newresponse, 'answer')) {
            return false;
        }
    }
	
	 public function summarise_response(array $response) {
		 $first = true;
		 $result = "";
		 foreach ($response as $index => $answer) {
			if ($answer != 0) {
                            $selanswer = $this->choices[$answer];
                            if ($first) {
                                    if($result != "") {
                                            $result .= "; ";
                                    }
                                    $result .= "{".$selanswer."; ";
                                    $first = false;
                            } else {
                                    $result .= $selanswer."}";
                                    $first = true;
                            }
                        }
		 }
		 return $result;
    }
	
	public function get_validation_error(array $response) {
            if ($this->is_complete_response($response)) {
                return '';
            }
            return get_string('responsenotcomplete', 'qtype_numberinterval');
        }
	
	
	public function grade_response(array $response) {
        $fraction = 1;
		$correctfcount = 0;
		$respfcount = 0;
		// Check if response contains all the correct answer
		foreach ($this->rightchoices as $index => $answer) {
            if(array_key_exists($this->field($index), $response)) {
				$correctfcount++;
				if($response[$this->field($index)] != $answer)
					$fraction = 0;
			} else {
				$fraction = 0;
			}
        }
		// If answer already is not correct we can leave out this part
		if ($fraction != 0) {
			// Check if there is any extra wrong answer added. If 
			// count of response answer non-empty fields is bigger 
			// than that of correct response - answer is not correct			
			foreach ($response as $index => $answer) {
				if(!empty($answer)) {
					$respfcount++;
				}
			}
			if ($correctfcount != $respfcount) {
				$fraction = 0;
			}
		}
        return array($fraction, 
			question_state::graded_state_for_fraction($fraction));
    }
}
