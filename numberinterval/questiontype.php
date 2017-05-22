<?php

/**
 * Question type class for the number interval question type.
 *
 * @package    qtype
 * @subpackage numberinterval
 * @copyright  2017 Uldis Dzilna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');


/**
 * The number interval question type.
 *
 * @copyright  2017 Uldis Dzilna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_numberinterval extends question_type {
     public function save_question_options($question) {
        global $DB;
        $context = $question->context;
        $result = new stdClass();

        $oldanswers = $DB->get_records('question_answers',
                array('question' => $question->id), 'id ASC');

        // Insert all the new answers.
        foreach ($question->choices as $key => $choice) {

            if (trim($choice['answer']) == '') {
                continue;
            }
						
            $feedback = $choice['answerplace'];

            if ($answer = array_shift($oldanswers)) {
                $answer->answer = $choice['answer'];
                $answer->feedback = $feedback;
                $DB->update_record('question_answers', $answer);

            } else {
                $answer = new stdClass();
                $answer->question = $question->id;
                $answer->answer = $choice['answer'];
                $answer->answerformat = FORMAT_HTML;
                $answer->fraction = 0;
                $answer->feedback = $feedback;
                $answer->feedbackformat = 0;
                $DB->insert_record('question_answers', $answer);
            }
        }

        // Delete old answer records.
        foreach ($oldanswers as $oa) {
            $DB->delete_records('question_answers', array('id' => $oa->id));
        }

    }
	
	function initialise_question_instance(question_definition $question, $questiondata) {
		parent::initialise_question_instance($question, $questiondata);

        $question->choices = array();
        $question->rightchoices = array();

        // Store the choices in array.
        $i = 1;
		//$j = 1; //key for answers
        foreach ($questiondata->options->answers as $choicedata) {
            $question->choices[$i] = $choicedata->answer;
			
			if($choicedata->feedback > 0) {
				$question->rightchoices[$choicedata->feedback] = $i;
			}
            $i += 1;
        }
		
		$question->intervalcount = intval(get_string('intervalcount','qtype_numberinterval'));
	}
}
