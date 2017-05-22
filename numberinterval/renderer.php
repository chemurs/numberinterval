<?php

/**
 * numberinterval question renderer class.
 *
 * @package    qtype
 * @subpackage numberinterval
 * @copyright  2017 Uldis Dzilna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Generates the output for number interval questions.
 *
 * @copyright  2017 Uldis Dzilna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_numberinterval_renderer extends qtype_renderer {
	public function formulation_and_controls(question_attempt $qa,
            question_display_options $options) {
		
		$result = html_writer::tag('div', $qa->get_question()->format_questiontext($qa),
                array('class' => 'qtext'));
		$question = $qa->get_question();
		$result .= $this->add_explanation_text();
		$showHideButton = false;
		for($i=1,$j=1;$i<=$question->intervalcount;$i++) { 
			$divarray = array('id'=>$qa->get_qt_field_name('interval_'.$i));
			if ($i>1) {
				if (!($qa->get_last_qt_var($question->field($j)) || $qa->get_last_qt_var($question->field($j+1)))) {
					$divarray['style'] = 'display:none;';
				} else {
					$showHideButton = true;
				}
				
			}
			$result .= html_writer::start_tag('div', $divarray);
			$result .= ' '.get_string('intervalfrom', "qtype_numberinterval").' ';
			$result .= $this->add_select_field($qa, $j++, $options->readonly);
			$result .= ' '.get_string('intervalto', "qtype_numberinterval").' ';
			$result .= $this->add_select_field($qa, $j++, $options->readonly);
			
            $intervaljoin = get_string('intervaljoin', 'qtype_numberinterval');
            $joinarray = array('id'=>'interval_conj_'.$i);
			// Check if next interval will be visible. If yes, than do not hide the conjunction text.
			if (!($i<$question->intervalcount && ($qa->get_last_qt_var($question->field($j)) || $qa->get_last_qt_var($question->field($j+1))))) {
				$joinarray['style']='display:none';
			}	
			$result .= html_writer::tag('span', ' '.$intervaljoin, $joinarray);
			$result .= html_writer::end_tag('div');
			
		}
		$result .= $this->add_end_buttons($qa, $options->readonly, $showHideButton);
		//$result .= '<span id="numberIntervalCount" style="display:none">'.$question->intervalcount.'</span>';
		return $result;
    }
	
	private function add_select_field($qa, $index, $readonly) {
		$question = $qa->get_question();
		$selectoptions = $question->choices;
		$inputname = $qa->get_qt_field_name($question->field($index));
		$value = $qa->get_last_qt_var($question->field($index));
		//$index++;
		$choose = '&nbsp;';
		$attributes = array(
			'id'     => $inputname,
			 'class' => 'custom-select '
		);
		if ($readonly) {
			$attributes['disabled'] = 'disabled';
		}
		return html_writer::select($selectoptions, $inputname, $value, $choose, $attributes);
	}
	
	
	private function add_end_buttons($qa, $readonly, $showHideButton) {
		$result = '';
		if (!$readonly) {		
			$addButtonName = get_string('addbutton', 'qtype_numberinterval');
			$removeButtonName = get_string('removebutton', 'qtype_numberinterval');
			$result .= html_writer::start_tag('div', array('id'=>$qa->get_qt_field_name('endButtons'), 'class'=>'intervalButtonDiv'));
			$attributes = array (
				'type'=>'button',
				'class'=>"btn btn-primary intervalButton",
				'id'=>$qa->get_qt_field_name('addButton'),
				'onclick'=>'addInterval("'.$qa->get_qt_field_name('').'", '.$qa->get_question()->intervalcount.'); this.blur();'
			);
			$result .= html_writer::tag('button', 'Pievienot intervālu', $attributes);
			$attributes['id'] = $qa->get_qt_field_name('removeButton');
			$attributes['onclick'] = 'removeInterval("'.$qa->get_qt_field_name('').'", '.$qa->get_question()->intervalcount.'); this.blur();';
			if (!$showHideButton)
				$attributes['style'] = 'display:none';
			$result .= html_writer::tag('button', 'Noņemt intervālu', $attributes);
			//$result .= '<button type="button" class="btn btn-primary" id="addButton" onclick="addInterval('.$qa->get_qt_field_name('').'); this.blur();"></button>';
			//$result .= '<button type="button" class="btn btn-primary" id="removeButton" style="display:none;" onclick="removeInterval('.$qa->get_qt_field_name('').'); this.blur();">Noņemt intervālu</button>';
			$result .= html_writer::end_tag('div');
			global $CFG;
			$result .= '<script src="'.$CFG->wwwroot.'/question/type/numberinterval/module.js" type="text/javascript"/></script>';
		}
		return $result;
	}
	
	private function add_explanation_text() {
		$addButtonName = get_string('addbutton', 'qtype_numberinterval');
		$removeButtonName = get_string('removebutton', 'qtype_numberinterval');
		$explanationText = get_string('answerfillexplanation', 'qtype_numberinterval');
		$explanationText = str_replace ('{addbutton}', $addButtonName, $explanationText);
		$explanationText = str_replace ('{removebutton}', $removeButtonName, $explanationText);
		return html_writer::tag('p', $explanationText,
                array('class' => 'explanationText'));
	}
}

