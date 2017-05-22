<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Defines the editing form for the numberinterval question type.
 *
 * @package    qtype
 * @subpackage numberinterval
 * @copyright  2017 Uldis Dzilna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


class qtype_numberinterval_edit_form extends question_edit_form {
    
    
    // Initial choice count
    const CHOICE_COUNT_INIT = 5;
    // Number of choices added per "add choices" button click
    const CHOICE_COUNT_ADD = 2;
    // Maximum number of intervals that can be included in answer. Defined in "lang" file.
    private $intervalcount;
	
	protected function set_interval_count() {
		if (!isset($this->intervalcount))
			$this->intervalcount = intval(get_string('intervalcount','qtype_numberinterval'));
	}
	/**
     * Creates an array with elements for a choice group.
     *
     * @param object $mform The Moodle form we are working with
     * @return array Array for form elements
     */
    protected function choice_group($mform) {
		$this->set_interval_count();
		$options = array();
		$options[0] = "-";
        for ($i = 1; $i <= $this->intervalcount*2; $i += 1) {
            $options[$i] = $i;
        }
        $grouparray = array();
        $grouparray[] = $mform->createElement('text', 'answer',
                get_string('answer', 'qtype_numberinterval'), array('size' => 30, 'class' => 'tweakcss'));
        $grouparray[] = $mform->createElement('select', 'answerplace',
                get_string('answerplace', 'qtype_numberinterval'), $options);
        return $grouparray;
    }
	
	/**
     * Returns an array for form repeat options.
     *
     * @return array Array of repeate options
     */
    protected function repeated_options() {
        $repeatedoptions = array();
        $repeatedoptions['answerplace']['default'] = '-';
        $repeatedoptions['choices[answer]']['type'] = PARAM_RAW;
        return $repeatedoptions;
    }
	
	/**
     * Base class method. Intended for adding any question-type specific form fields.
     *
     * @param object $mform the form being built.
     */
	protected function definition_inner($mform) {
		$mform->addElement('header', 'choicehdr', get_string('choices', 'qtype_numberinterval'));
        $mform->setExpanded('choicehdr', 1);

        $textboxgroup = array();
        $textboxgroup[] = $mform->createElement('group', 'choices',
                get_string('choicex', 'qtype_numberinterval'), $this->choice_group($mform));

        if (isset($this->question->options)) {
            $countanswers = count($this->question->options->answers);
        } else {
            $countanswers = 0;
        }

        if ($this->question->formoptions->repeatelements) {
            //$defaultstartnumbers = QUESTION_NUMANS_START * 2;
            $repeatsatstart = max(self::CHOICE_COUNT_INIT, $countanswers);
        } else {
            $repeatsatstart = $countanswers;
        }

        $repeatedoptions = $this->repeated_options();
        $mform->setType('answer', PARAM_RAW);
        $this->repeat_elements($textboxgroup, $repeatsatstart, $repeatedoptions,
                'noanswers', 'addanswers', self::CHOICE_COUNT_ADD,
                get_string('addmorechoiceblanks', 'qtype_numberinterval'), true);
	}
	
	/**
     * Perform an preprocessing needed on the data passed to {@link set_data()}
     * before it is used to initialise the form.
     * @param object $question the data being passed to the form.
     * @return object $question the modified data.
     */
	public function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        
        if (!empty($question->options->answers)) {
            $key = 0;
            foreach ($question->options->answers as $answer) {
                $question = $this->data_preprocessing_choice($question, $answer, $key);
                $key++;
            }
        }

        return $question;
    }
	
	/** Perform data preprocessing for individual choice. Read data from $answer
	 *  object for a separate row.
	 */
    protected function data_preprocessing_choice($question, $answer, $key) {
        // See comment in data_preprocessing_answers.
        unset($this->_form->_defaultValues['choices[$key][answerplace]']);
        //unset($this->_form->_defaultValues['choices['.$key.'][answerplace]']);
	//	unset($this->_form->_defaultValues['[answerplace['.$key.']]']);
	//	unset($this->_form->_defaultValues['[answerplace[$key]]']);
        $question->choices[$key]['answer'] = $answer->answer;
		// In the database table the values are stored from 0 to n, but in the combobox from 1 to n+1
        $question->choices[$key]['answerplace'] = $answer->feedback;
        return $question;
    }
	
	/**
     * 
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @return array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK.
     */
	public function validation($data, $files) {
        $errors = parent::validation($data, $files);       
        $choices = $data['choices'];
        $checkchoices = array();
		
		// First collect all the selected answerplaces. Store them in $checkchoices as indexes.
		// The values arethe keys in $choices array.
        foreach ($choices as $key => $choice) {
            
			
			$curranschoice = $choice['answerplace'];
			if ($curranschoice > 0) {
				if (array_key_exists($curranschoice, $checkchoices)) {
					$errStr=get_string('answerplacemultiusage','qtype_numberinterval');
					$errStr = str_replace('{no}', $curranschoice, $errStr);
					$errors['choices['.$key.']']=$errStr;
				}
				$checkchoices[$curranschoice] = $key;
			}
        }
		
		// The first should always be selected, otherwise there is no interval.
		if (!array_key_exists(1, $checkchoices) || !array_key_exists(2, $checkchoices)) {
			$errors['choices[0]'] = get_string('firstintervalmissing','qtype_numberinterval');
		} else {
			$ismissing = false;
			$missinglist = '';
			$missingerror = false;
			$lastindex = 0;
			$this->set_interval_count();
			// If the first two are selected, check all the rest.
			// Check if we have a missing value inbetween.
			for($i = 3; $i <= $this->intervalcount*2; $i++) {
				// If a key is missing, either it's the end, or we have an error
				// Collect the missing places in variable $missinglist
				if (!array_key_exists($i, $checkchoices)) {
					if(!$ismissing) {
						$missinglist = $i;
						$ismissing = true;
					} else {
						$missinglist .= ", ". $i;
					}
				} else {
					$lastindex = $i;
					// If the key is not missing, check has there been a missing key before.
					// If there has, add the missing keys to the error message. And display on the first missing field
					if ($missinglist != '') {
						$currerror = str_replace ('{$list}', $missinglist, get_string('missingplacelist', 'qtype_numberinterval'));
						$currerror = str_replace ('{no}', $i, $currerror);
						$errors['choices['.$checkchoices[$i].']'] = $currerror;
						$missingerror = true;
						$missinglist = '';
						$ismissing = false;
					}
				}
			}
			// Check if last element is odd place and if it has an even pair
			if($lastindex % 2 == 1 && !array_key_exists($lastindex+1, $checkchoices)) {
				$errors['choices['.$checkchoices[$lastindex].']'] = str_replace ('{no}', $lastindex+1, get_string('missingpair', 'qtype_numberinterval'));
			}
		}
		
		//check if each answerplace is used not more than once.
		
		
        return $errors;
    }

	public function qtype() {
        return 'numberinterval';
    }
}
