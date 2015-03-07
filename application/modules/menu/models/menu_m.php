<?
class menu_m extends CI_Model
{

	protected $table='tbl_menus';

	public $rules=array(
		array(
			'field'=>'parent_id',
			'label'=>'parent menu',
			'rules'=>'trim|number|xss_clean'
			),
		array(
			'field'=>'page_type_id',
			'label'=>'page type',
			'rules'=>'trim|number|xss_clean'
			),
		array(
			'field'=>'name',
			'label'=>'menu Name',
			'rules'=>'trim|required|xss_clean'
			),
		array(
			'field'=>'content',
			'label'=>'menu content',
			'rules'=>'trim|xss_clean'
			),
		);

	const PENDING=0;
	const ACTIVE=1;
	const BLOCKED=2;
	const DELETED=3;

	public static function status($key=null){
		$status=array(
			self::PENDING=>'Pending',
			self::ACTIVE=>'Active',
			self::BLOCKED=>'Blocked',
			self::DELETED=>'Deleted',
			);
		if(isset($key)) return $status[$key];
		return $status;
	}

	public static function actions($key=null){
		$actions=array(
			self::PENDING=>'Pending',
			self::ACTIVE=>'Active',
			self::BLOCKED=>'Block',
			self::DELETED=>'Delete',
			);
		if(isset($key)) return $actions[$key];
		return $actions;
	}


	protected $path;
	public function __construct(){
		$this->path=base_url()."uploads/pics/testimonials/";
	}

	function read_all()
	{
		$this->db->select()
		->from($this->table)
		->where("status != ",3)
		->order_by('parent_id','asc')
		->order_by('order','asc');
		$rs=$this->db->get();
		return $rs->result_array();				 
	}

	//order
	function buildTree(array $elements, $parentId = 0) {
		$branch = array();

		foreach ($elements as $element) {
			if ($element['parent_id'] == $parentId) {
				$children = $this->buildTree($elements, $element['id']);
				if ($children) {
					$element['children'] = $children;
				}
				$branch[] = $element;
			}
		}

		return $branch;
	}
	function read_menus_for_ordering()
	{
		$this->db->select();
		$this->db->from($this->table);
		$this->db->order_by("order", "asc");
		$query = $this->db->get();
		$menus=$query->result_array();
		$final_menus = $this->buildTree($menus);
		// show_pre($final_menus);
		return ($final_menus);
	}
	public function save_order($menus)
	{
		$response['success']=false;
		$response['data']='Error Processing Request';
		try {
			if (count($menus)) {
				foreach ($menus as $order => $menu) {
					$id=$menu['item_id'];
					if ($id) {
						$data = array(
							'parent_id' =>  $menu['parent_id']?$menu['parent_id']:NULL,
							'order' => $order);
						$this->db->set($data)->where('id',$id)->update($this->table);
					}
				}
				$response['success']=true;
				$response['data']="menu order successfully update";
			}
		} catch (Exception $e) {
			echo $e->getMessage();
			$response['data']=$e->getMessage();
		}
		return $response;
	}


	//order


	public function get_parents()
	{
		$this->db->select('id,name')->from($this->table)->where("status =".self::ACTIVE." and parent_id is NULL");
		$rs=$this->db->get();
		return $rs->result_array();				 
	}

	public function get_parent_name($id=NULL)
	{
		$this->db->select('name')->from($this->table)->where("id =$id")->limit('1');
		$rs=$this->db->get();
		return $rs->row('name');				 				 
	}

	function count_rows()
	{
		$this->db->select()
		->from($this->table)
		->where("status != ",3)
		->order_by('id','desc');
		$rs=$this->db->get();
		return $rs->num_rows();				 
	}	
	function create_row($data)
	{
		$this->db->insert($this->table
			,$data);
	}
	function read_row($id)
	{
		$this->db->select()
		->from($this->table)
		->where('id',$id);
		$rs=$this->db->get();
		return ($rs->first_row('array'));
	}
	public function read_row_by_slug($slug='')
	{
		if(!$slug) return false;
		$this->db->select()
		->from($this->table)
		->where('slug',$slug);
		$rs=$this->db->get();
		if($rs->num_rows()==0)
			return false;
		return ($rs->first_row('array'));
	}





	function update_row($id,$data)
	{
		try {
			$this->db->where('id',$id);
			$this->db->update($this->table,$data);
		} catch (Exception $e) {
			echo $e->getMessage();			
		}
	}
	function delete_row($id)
	{
		$this->db->where('id',$id);
		$this->db->update($this->table,array('status' =>self::DELETED));
		
	}

	public function set_rules(array $escape_rules=NUll){
		if($escape_rules && is_array($escape_rules)){
			foreach($this->rules as $rule){
				if(in_array($rule['field'],$escape_rules)) continue;
				$applied_rules[]=$rule;
			}
			return $applied_rules;
		}
		return $this->rules;
	}



}
?>