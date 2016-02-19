<?php
/**
 * Form class.
 */
namespace Bluejacket\Web;
class Form
{
	public $out;
	public $model;

	function __construct($model, $options=array(
			'class' => null,
			'id' => null,
			'action' => null,
			'method' => null,
			'enctype' => 'multipart/form-data'
		)){
		$this->out = '<form';
		@$this->out .= $options['class'] != null ? ' class="'.$options['class'].'"' : null;
		@$this->out .= $options['id'] != null ? ' id="'.$options['id'].'"' : null;
		@$this->out .= $options['action'] != null ? ' action="'.$options['action'].'"' : null;
		@$this->out .= $options['method'] != null ? ' method="'.$options['method'].'"' : null;
		@$this->out .= $options['enctype'] != null ? ' enctype="'.$options['enctype'].'"' : null;
		$this->out .= '>';
	}

	/**
	 * generate function.
	 *
	 * @access public
	 * @param array $fields (default: array())
	 * @return void
	 */
	function generate($fields=array()){
		foreach($fields as $field => $options){
			switch($options['type']){
			case 'input':
				$this->out.=$this->input(array(
						'class' => $options['class'],
						'id' => $options['id'],
						'name' => $field,
						'placeholder' => $options['placeholder'],
						'type' => $options['options']['type'],
						'label' => $options['label'],
						'value' => $options['value'],
						'html' => $options['html'],
						'checked' => $options['checked'],
						'append' => $options['append'],
						'extra' => $options['extra']
					));
				break;
			case 'option':
				$this->out.=$this->option($options['data'],array(
						'class' => $options['class'],
						'id' => $options['id'],
						'name' => $field,
						'data' => $options['data'],
						'html' => $options['html'],
						'label' => $options['label'],
						'selected' => $options['selected'],
						'append' => $options['append'],
						'extra' => $options['extra']
					));
				break;

			case 'button':
				$this->out.=$this->button(array(
						'class' => $options['class'],
						'id' => $options['id'],
						'name' => $field,
						'value' => $options['value'],
						'type' => $options['options']['type'],
						'onclick' => $onclick,
						'html' => $options['html'],
						'append' => $options['append'],
						'extra' => $options['extra']
					));
				break;

			case 'textarea':
				$this->out.=$this->textarea(array(
						'class' => $options['class'],
						'id' => $options['id'],
						'name' => $field,
						'label' => $options['label'],
						'value' => $options['value'],
						'html' => $options['html'],
						'append' => $options['append'],
						'extra' => $options['extra']
					));
				break;
			case 'label':
				$this->out.=$this->label(array(
						'class' => $options['class'],
						'id' => $options['id'],
						'append' => $options['append'],
						'content' => $options['content'],
						'html' => $options['html'],
						'extra' => $options['extra']
					));
				break;
			}
		}
	}


	/**
	 * input function.
	 *
	 * @access public
	 * @param array $options (default: array())
	 * @return void
	 */
	function input($options=array()){
		@$out = $options['label'] != null ? "<label>".$options['label']."</label>" : null;
		@$out .= "<input";
		@$out .= $options['class'] != null ? ' class="'.$options['class'].'"' : null;
		@$out .= $options['id'] != null ? ' id="'.$options['id'].'"' : null;
		@$out .= $options['name'] != null ? ' name="'.$options['name'].'"' : null;
		@$out .= $options['type'] != null ? ' type="'.$options['type'].'"' : null;
		@$out .= $options['value'] != null ? ' value="'.$options['value'].'"' : null;
		@$out .= $options['placeholder'] != null ? ' placeholder="'.$options['placeholder'].'"' : null;

		if($options['type'] == "checkbox" && $options['checked']){
			@$out .= "checked";
		}

		if(is_array($options['extra'])){
			foreach($options['extra'] as $key => $val){
				@$out .= $key."='".$val."'";
			}
		}

		$out .= " />";
		@$out .= $options['append'] != null ? $options['append'] : null;
		if($options['html']) $out = str_replace('%form%',$out,$options['html']);
		return $out;
	}

	/**
	 * option function.
	 *
	 * @access public
	 * @param array $array (default: array())
	 * @param array $options (default: array())
	 * @return void
	 */
	function option($array=array(),$options=array()){
		@$out = $options['label'] != null ? "<label>".$options['label']."</label>" : null;
		@$out .= "<select";
		@$out .= $options['class'] != null ? ' class="'.$options['class'].'"' : null;
		@$out .= $options['id'] != null ? ' id="'.$options['id'].'"' : null;
		@$out .= $options['name'] != null ? ' name="'.$options['name'].'"' : null;

		if(is_array($options['extra'])){
			foreach($options['extra'] as $key => $val){
				@$out .= $key."='".$val."'";
			}
		}

		@$out .= " >";
		@$out .= $options['append'] != null ? $options['append'] : null;
		if(is_array($array)){
			foreach($array as $key => $val){
				if(@$options['selected'] == $val){
					$selected = 'selected';
				}else $selected = null;

				$out .= "<option value='".$val."' ".$selected.">".$key."</option>";
			}
		}

		@$out .= "</select>";
		if($options['html']) $out = str_replace('%form%',$out,$options['html']);
		return $out;
	}

	/**
	 * label function.
	 *
	 * @access public
	 * @param array $options (default: array())
	 * @return void
	 */
	function label($options=array()){
		$out = "<label";
		@$out .= $options['class'] != null ? ' class="'.$options['class'].'"' : null;
		@$out .= $options['id'] != null ? ' id="'.$options['id'].'"' : null;
		if(is_array($options['extra'])){
			foreach($options['extra'] as $key => $val){
				@$out .= $key."='".$val."'";
			}
		}
		$out .= ">";
		@$out .= $options['content'] != null ? $options['content'] : null;
		$out .= "</label>";
		@$out .= $options['append'] != null ? $options['append'] : null;
		if($options['html']) $out = str_replace('%form%',$out,$options['html']);
		return $out;
	}

	/**
	 * button function.
	 *
	 * @access public
	 * @param array $options (default: array())
	 * @return void
	 */
	function button($options=array()){
		$out = "<button";
		@$out .= $options['class'] != null ? ' class="'.$options['class'].'"' : null;
		@$out .= $options['id'] != null ? ' id="'.$options['id'].'"' : null;
		//@$out .= $options['name'] != null ? ' name="'.$options['name'].'"' : null;
		@$out .= $options['type'] != null ? ' type="'.$options['type'].'"' : null;
		@$out .= $options['onclick'] != null ? ' onclick="'.$options['onclick'].'"' : null;
		if(is_array($options['extra'])){
			foreach($options['extra'] as $key => $val){
				@$out .= $key."='".$val."'";
			}
		}
		$out .= ">";
		@$out .= $options['value'] != null ? $options['value'] : null;
		$out .= "</button>";
		@$out .= $options['append'] != null ? $options['append'] : null;
		if($options['html']) $out = str_replace('%form%',$out,$options['html']);
		return $out;
	}

	/**
	 * textarea function.
	 *
	 * @access public
	 * @param array $options (default: array())
	 * @return void
	 */
	function textarea($options=array()){
		$out = null;
		@$out .= $options['label'] != null ? "<label>".$options['label']."</label>" : null;
		@$out .= "<textarea";
		@$out .= $options['class'] != null ? ' class="'.$options['class'].'"' : null;
		@$out .= $options['id'] != null ? ' id="'.$options['id'].'"' : null;
		@$out .= $options['name'] != null ? ' name="'.$options['name'].'"' : null;
		if(is_array($options['extra'])){
			foreach($options['extra'] as $key => $val){
				@$out .= $key."='".$val."'";
			}
		}
		$out .= ">";
		@$out .= $options['value'] != null ? $options['value'] : null;
		$out .= "</textarea>";
		@$out .= $options['append'] != null ? $options['append'] : null;
		if($options['html']) $out = str_replace('%form%',$out,$options['html']);
		return $out;
	}


	/**
	 * html function.
	 *
	 * @access public
	 * @param mixed $html
	 * @return void
	 */
	function html($html){
		$this->out .= $html;
	}

	/**
	 * end function.
	 *
	 * @access public
	 * @return void
	 */
	function end(){
		$this->out .= "</form>";
	}

	/**
	 * output function.
	 *
	 * @access public
	 * @return void
	 */
	function output(){
		return $this->out;
	}
}