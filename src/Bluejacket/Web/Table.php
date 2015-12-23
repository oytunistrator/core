<?php
/**
 * Table class.
 */
namespace Bluejacket\Web;
class Table
{
	/**
	 * __construct function.
	 *
	 * @access public
	 * @param mixed $model
	 * @param array $options (default: array())
	 * @return void
	 */
	function __construct(array $properties=array()){
		if(is_array($properties)){
			foreach($properties as $key => $value){
				$this->{$key} = $value;
			}
		}

		$error = new \Framework\Core\Error();
		if($error->checkClass($this->model)){
			$this->_model = new $this->model;
		}else{
			$error->show("Model not found:".$this->model,1);
		}



		if(is_array($this->search)){
			foreach ($this->_model->search() as $key) {
				$sq[$key] = $this->search[0];
			}
			$this->_model->db->search($sq,$this->search[1]);
			$this->_model->db->query();
			if($this->_model->db->output){
				$this->_arr = $this->_model->db->output->fetchAll();
				$this->_count = count($this->arr);
			}else{
				$this->_model->db->select();
				$this->_model->db->query();
				$this->_arr = $this->_model->db->output->fetchAll();
				$this->_count = $this->_model->count();
			}
		}else{
			$this->_model->db->select();
			if(is_array($this->where)) $this->_model->db->where($this->where);
			if(is_array($this->orderBy)) $this->_model->db->orderBy($this->orderBy[0],$this->orderBy[1]);
			if(isset($this->groupBy)) $this->_model->db->groupBy($this->groupBy);
			if(is_array($this->limit)){
				$this->_start = $this->limit[0];
				$this->_model->db->limit($this->limit[0],$this->limit[1]);
			}
			$this->_model->db->query();
			$this->_arr = $this->_model->db->output->fetchAll();
			$this->_count = $this->_model->count($this->where);
		}

	}




	/**
	 * generate function.
	 *
	 * @access public
	 * @return void
	 */
	public function generate($array = array()){
		$this->_out .= isset($array['start']) ? $array['start'] : "";

		if($this->_model->db->output){
			$this->_out .= "<table";
			$this->_out .= $this->class != null ? " class='".$this->class."'" : null;
			$this->_out .= $this->id != null ? " id='".$this->id."'" : null;
			$this->_out .= ">";

			if(count($this->_arr)==0){
				if(is_array($this->error)){
					$this->_out = "<div ".($this->error['class'] != null ? "class=\"".$this->error['class']."\"" : null)." ".($this->error['id'] != null ? "id=\"".$this->error['id']."\"" : null).">".($this->error['content'] != null ? $this->error['content'] : null)."</div>";
					$this->_error = true;
					return;
				}
			}

			if(is_array($this->headers)){
				$this->_out .= "<thead><tr>";
				if(isset($this->showLineCount) && $this->showLineCount == true){
					$this->_out.="<th class='row id'>#</th>";
				}
				foreach($this->headers as $row => $customname){
					$this->_out .= "<th>".$customname."</th>";
				}



				if(is_array($this->actions)) $this->_out .= "<th class='row actions'></th>";
				$this->_out .= "</tr></thead>";
			}

			$primaryKey = $this->_model->getPrimaryKey();
			$arr = $this->_arr;

			/* if($actions != null) $this->out .= "<th>".$actions."</th>"; */
			$this->_out .= "<tbody>";
			$list = array();
			$i=0;
			while($i<count($arr)){
				$this->_out.= "<tr>";
				if(isset($this->showLineCount) && $this->showLineCount == true){
					$this->_out.="<td class='row id id-".($i+1+$this->_start)."'>".($i+1+$this->_start)."</td>";
				}
				if(is_array($this->headers)){
					foreach($this->headers as $row => $customname){
						if(is_array($this->links)){
							foreach($this->links as $rw => $opts){
								if($rw == $row){
									$submodel = new $opts['model'];
									$submodel->get(array($opts['extract'] => $arr[$i][$opts['value']]));

									if(@is_numeric($submodel->id)){
										$newurl = str_replace("%model%",$opts['model'],$opts['url']);
										$newurl = str_replace("%id%",$submodel->id,$newurl);
										$newurl = str_replace("%extract%",$submodel->$opts['extract'],$newurl);
										$res_submodel_out = $submodel->$opts['output'];

										/* eğer verinin tamamını almak istemezse bir özellik daha ekle verinin tamamını almasın. */

										if($opts['if']){
											$if_model = $opts['if']['model'];
											$if_extract = $opts['if']['extract'];
											$if_value = $opts['if']['value'];
											$if_output = $opts['if']['output'];

											$if_model_class = new $if_model;
											$if_model_class->get(array($if_extract => $arr[$i][$if_value]));

											$newurl = str_replace("%model%",$if_model,$opts['url']);
											$newurl = str_replace("%id%",$if_model_class->id,$newurl);
											$newurl = str_replace("%extract%",$if_model->$if_extract,$newurl);


											switch($opts['if']['option']){
												case 'empty':
													if(empty($submodel->$opts['output']) || is_null($submodel->$opts['output']) || $submodel->$opts['output'] == ""){
														$res_submodel_out = $if_model_class->$if_output;
													}
													break;
												case 'numeric':
													if(!is_numeric($submodel->$opts['output'])){
														$res_submodel_out = $if_model_class->$if_output;
													}
													break;
												case 'isset':
													if(!isset($submodel->$opts['output'])){
														$res_submodel_out = $if_model_class->$if_output;
													}
													break;
											}
										}

										if(is_array($opts['crop'])){
											$cropStart = $opts['crop'][0];
											$cropEnd = $opts['crop'][1];
											if(strlen($res_submodel_out) > $cropEnd){
												$res_submodel_out = substr($res_submodel_out, $cropStart, $cropEnd);
												$res_submodel_out .= $opts['crop_char'];
											}
										}

										$this->_out.="<td><a href=".$newurl.">".$res_submodel_out."</a></td>";
									}else if(isset($opts['custom'])){
											$this->_out.="<td>".str_replace("%primaryKey%",$arr[$i][$primaryKey],$opts['custom'])."</td>";
									}else if($arr[$i][$row]){
										$this->_out.="<td>".$arr[$i][$row]."</td>";
									}else{
										$this->_out.="<td></td>";
									}

									$list[] = $row;
								}
							}
						}
						if(!in_array($row,$list)){
							$this->_out.="<td>".$arr[$i][$row]."</td>";
						}
					}

					$rev = null;
					if(is_array($this->actions)){
						$this->_out .= "<td>";
						foreach($this->actions as $action){
							$this->_model->db->columns();
							$this->_model->db->query();
							$res = $this->_model->db->output->fetchAll();
							foreach($res as $v){
								if(@!in_array($v['Field'],$rev)) $action = str_replace("%".$v['Field']."%",$arr[$i][$v['Field']],$action);
								@$rev[]=$v['Field'];
							}
							$this->_out .= str_replace("%primaryKey%",$arr[$i][$primaryKey],$action);
						}
						$this->_out .= "</td>";
					}

					$this->_out.= "</tr>";

				}
				$i++;
			}
			$this->_out .= "</tbody></table>";
		}else{
			if(is_array($this->error)){
				$this->_out = "<div ".($this->error['class'] != null ? "class=\"".$this->error['class']."\"" : null)." ".($this->error['id'] != null ? "id=\"".$this->error['id']."\"" : null).">".($this->error['content'] != null ? $this->error['content'] : null)."</div>";
				$this->_error = true;
			}
		}
		$this->_out .= isset($array['end']) ? $array['end'] : "";
	}

	/**
	 * pagination function.
	 *
	 * @access public
	 * @param mixed $options (array)
	 * @return void
	 */
	public function pagination($array = array()){
		$count = $this->_count;
		@$slice = $count/$this->pagination['count'];
		@$slice = ceil($slice);
		@$mod = $count % $this->pagination['count'];
		if($slice >= 1){
			$this->_out .= isset($array['start']) ? $array['start'] : "";

			$button = null;

			if(isset($_GET['page'])) $page = $_GET['page']!=0  ? $_GET['page'] : 1;
			else $page = 1;

			$prev = ($page-1) <= 0 ? 1 : $page;
			$next = $page+1;


			if($prev > 1){
				if(isset($this->pagination['link'])){
					$button .="<a href=";
					$button .= str_replace("%page%",$page-1,$this->pagination['url']);
					$button .= isset($this->pagination['link']['class']) && $this->pagination['link']['class'] != null ? " class='".$this->pagination['link']['class'].$lnk_reverse."'" : null;
					$button .= isset($this->pagination['link']['id']) && $this->pagination['link']['id'] != null ? " id='".$this->pagination['link']['class']."'" : null;
					$button .=">".$this->pagination['prev']."</a>";
				}
			}

			$i=1;
			while($i<=$slice){
				if(@$page){
					@$button_reverse = $_GET['page']==$i ? $this->pagination['button']['reverse'] : null;
					@$lnk_reverse = $_GET['page']==$i ? $this->pagination['link']['reverse'] : null;
				}else{
					@$button_reverse = $i==1 ? $this->pagination['button']['reverse'] : null;
					@$lnk_reverse = $i==1 ? $this->pagination['link']['reverse'] : null;
				}

				if(isset($this->pagination['link'])){
					$button .="<a href=";
					$button .= str_replace("%page%",$i,$this->pagination['url']);
					$button .= isset($this->pagination['link']['class']) && $this->pagination['link']['class'] != null ? " class='".$this->pagination['link']['class'].$lnk_reverse."'" : null;
					$button .= isset($this->pagination['link']['id']) && $this->pagination['link']['id'] != null ? " id='".$this->pagination['link']['class']."'" : null;
					$button .=">".$i."</a>";

					if(isset($this->pagination['slice']) && $this->pagination['slice'] == $i){
						$button .="<a href=";
						$button .= str_replace("%page%",$page-$this->pagination['slice']-1,$this->pagination['url']);
						$button .= isset($this->pagination['link']['class']) && $this->pagination['link']['class'] != null ? " class='".$this->pagination['link']['class'].$lnk_reverse."'" : null;
						$button .= isset($this->pagination['link']['id']) && $this->pagination['link']['id'] != null ? " id='".$this->pagination['link']['class']."'" : null;
						$button .=">...</a>";

						$button .="<a href=";
						$button .= str_replace("%page%",$page,$this->pagination['url']);
						$button .= isset($this->pagination['link']['class']) && $this->pagination['link']['class'] != null ? " class='".$this->pagination['link']['class'].$lnk_reverse."'" : null;
						$button .= isset($this->pagination['link']['id']) && $this->pagination['link']['id'] != null ? " id='".$this->pagination['link']['class']."'" : null;
						$button .=">".$page."</a>";

						$button .="<a href=";
						$button .= str_replace("%page%",$page+$this->pagination['slice']+1,$this->pagination['url']);
						$button .= isset($this->pagination['link']['class']) && $this->pagination['link']['class'] != null ? " class='".$this->pagination['link']['class'].$lnk_reverse."'" : null;
						$button .= isset($this->pagination['link']['id']) && $this->pagination['link']['id'] != null ? " id='".$this->pagination['link']['class']."'" : null;
						$button .=">...</a>";


						break;
					}
				}
				$i++;
			}

			if(@$this->pagination['next'] != null && $slice != $page){
				if(isset($this->pagination['link'])){
					$button .="<a href=";
					$button .= str_replace("%page%",$page+1,$this->pagination['url']);
					$button .= isset($this->pagination['link']['class']) && $this->pagination['link']['class'] != null ? " class='".$this->pagination['link']['class'].$lnk_reverse."'" : null;
					$button .= isset($this->pagination['link']['id']) && $this->pagination['link']['id'] != null ? " id='".$this->pagination['link']['class']."'" : null;
					$button .=">".$this->pagination['next']."</a>";
				}
			}

			$this->_out .= isset($array['end']) ? $array['end'] : "";

			$this->_out .= str_replace("%buttons%",$button,$this->pagination['html']);
		}
	}


	/**
	 * html function.
	 *
	 * @access public
	 * @param mixed $html
	 * @return void
	 */
	public function html($html){
		$this->_out.=$html;
	}


	/**
	 * output function.
	 *
	 * @access public
	 * @return void
	 */
	public function output(){
		return $this->_out;
	}
}
?>
