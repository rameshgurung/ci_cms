<?
class article extends Admin_Controller
{
	const MODULE='article/';

	function __construct()
	{
		parent::__construct();
		if(!permission_permit(['administrator-article'])) redirect_to_dashboard();
		$this->load->helper(array('article','category/category'));
		$this->load->model('article_m');
		$this->load->model('category/category_m');
		$this->template_data['article_m']=$this->article_m;
		$this->template_data['category_m']=$this->category_m;
		$this->template_data['actions']=article_m::actions();
		$this->template_data['link']=base_url().self::MODULE;
		$this->template_data['categories']=$this->category_m->read_all($this->category_m->count_rows());
		$this->template_data['rows']=$this->article_m->read_all($this->article_m->count_rows());
		$this->breadcrumb->append_crumb('List Articles',base_url().self::MODULE.'index');
	}

	function index($offset=0)
	{
		if(!permission_permit(['list-article'])) redirect_to_dashboard();
		$per_page=10;
		$total_rows=$this->article_m->count_rows();
		$this->template_data['rows']=$this->article_m->read_all($per_page,$offset);
		if($total_rows>$per_page){
			$this->load->library('pagination');
			$config['base_url']=base_url().self::MODULE."index";
			$config['total_rows']=$total_rows;
			$config['per_page']=$per_page;
			$config['prev']='Previous';
			$config['next']='Next';
			$this->pagination->initialize($config);
			$this->template_data['pages']=$this->pagination->create_links();
		}
		$this->template_data['offset']=$offset;
		$this->template_data['subview']=self::MODULE.'list';
		$this->load->view('admin/main_layout',$this->template_data);
	}

	function add()
	{
		try {
			if(!permission_permit(array('list-article','add-article'))) $this->controller_redirect('Permissioin Denied');
			if($this->input->post())
			{
				$this->form_validation->set_rules($this->article_m->set_rules(array('status')));
				if($this->form_validation->run($this)===TRUE)
				{
					$current_user=current_loggedin_user();
					$this->template_data['insert_data']=array(
						'category_id'=>$this->input->post('category'),
						'name'=>$this->input->post('name'),
						'slug'=>get_slug($this->input->post('name')),
						'content'=>$this->input->post('content'),
						'image_title'=>$this->input->post('image_title')?$this->input->post('image_title'):NULL,
						'video_title'=>$this->input->post('video_title')?$this->input->post('video_title'):NULL,
						'embed_code'=>$this->input->post('embed_code')?$this->input->post('embed_code'):NULL,
						'meta_desc'=>$this->input->post('meta_description')?$this->input->post('meta_description'):NULL,
						'meta_key'=>$this->input->post('meta_keywords')?$this->input->post('meta_keywords'):NULL,
						'meta_robots'=>$this->input->post('meta_robots')?$this->input->post('meta_robots'):NULL,
						'author'=>$current_user['id'],
						'status'=>0,
						);
					if($_FILES['image']['name']){
						$this->template_data['update_data']['image']=$_FILES['image']['name'];
						$path=get_relative_upload_file_path();
						$path.=article_m::file_path;
						upload_picture($path,'image');
					}
					if($_FILES['video']['name']){
						$this->template_data['update_data']['video']=$_FILES['video']['name'];
						$video_path=get_relative_upload_video_path();
						$video_path.=article_m::file_path;
						upload_video($video_path,'video');
					}$this->article_m->create_row($this->template_data['insert_data']);
					$this->session->set_flashdata('success', 'article added successfully');
					$this->controller_redirect();				
				}
			}			
			$this->breadcrumb->append_crumb('Add','add');
			$this->template_data['subview']=self::MODULE.'add';
			$this->load->view('admin/main_layout',$this->template_data);
		} catch (Exception $e) {
			$this->session->set_flashdata('error', 'Couldnt add article '.$e->getMessage());
			$this->controller_redirect();
		}
	}

	function edit($slug=FALSE)
	{
		try {
			if(!permission_permit(array('list-article','edit-article'))) throw new Exception("Permissioin Denied", 1);
			if(!$slug) throw new Exception("Error Processing Request", 1);
			$response=$this->get($slug);
			if(!$response['success']) throw new Exception($response['data'], 1);
			$this->template_data['row']=$response['data'];
			$id=$response['data']['id'];			
			if($this->input->post())
			{
				$this->form_validation->set_rules($this->article_m->set_rules());
				if($this->form_validation->run($this)===TRUE)
				{
					$current_user=current_loggedin_user();
					$this->template_data['update_data']=array(
						'category_id'=>$this->input->post('category'),
						'name'=>$this->input->post('name'),
						'slug'=>get_slug($this->input->post('name')),
						'content'=>$this->input->post('content'),
						'image_title'=>$this->input->post('image_title')?$this->input->post('image_title'):NULL,
						'video_title'=>$this->input->post('video_title')?$this->input->post('video_title'):NULL,
						'video_url'=>$this->input->post('video_url')?$this->input->post('video_url'):NULL,
						'embed_code'=>$this->input->post('embed_code')?$this->input->post('embed_code'):NULL,
						'meta_desc'=>$this->input->post('meta_description')?$this->input->post('meta_description'):NULL,
						'meta_key'=>$this->input->post('meta_keywords')?$this->input->post('meta_keywords'):NULL,
						'meta_robots'=>$this->input->post('meta_robots')?$this->input->post('meta_robots'):NULL,
						'updated_at'=>date('Y-m-d H:i:s'),
						'modified_by'=>$current_user['id'],
						);

					if($_FILES['image']['name']){
						$this->template_data['update_data']['image']=$_FILES['image']['name'];
						$path=get_relative_upload_file_path();
						$path.=article_m::file_path;
						upload_picture($path,'image');
					}
					if($_FILES['video']['name']){
						$this->template_data['update_data']['video']=$_FILES['video']['name'];
						$video_path=get_relative_upload_video_path();
						$video_path.=article_m::file_path;
						upload_video($video_path,'video');
					}
					$this->article_m->update_row($id,$this->template_data['update_data']);
					$this->session->set_flashdata('success', 'article updated successfully');
					$this->controller_redirect();				
				}
				else{
					throw new Exception("Could not add article <hr/>");
				}
			}			
			$this->breadcrumb->append_crumb('Edit','edit');
			$this->template_data['subview']=self::MODULE.'edit';
			$this->load->view('admin/main_layout',$this->template_data);
		} catch (Exception $e) {
			$this->controller_redirect('Couldnt edit article '.$e->getMessage());
		}
	}

	function publish($slug=NULL){
		try{
			if(!permission_permit(array('activate-article'))) $this->controller_redirect('Permissioin Denied');
			if(!$slug) throw new Exception('Invalid paramter');
			$response=$this->get($slug);
			if(!$response['success']) throw new Exception($response['data'], 1);
			$this->template_data=array('status'=>article_m::PUBLISHED);
			$this->article_m->update_row($response['data']['id'],$this->template_data);
			$this->session->set_flashdata('success', 'article published successfully');
		}
		catch(Exception $e){
			$this->session->set_flashdata('error', 'article not published '.$e->getMessage());
		}
		$this->controller_redirect();				
	}


	function unpublish($slug=NULL){
		try{
			if(!permission_permit(array('block-article'))) $this->controller_redirect('Permissioin Denied');
			$response=$this->get($slug);
			if(!$response['success']) throw new Exception($response['data'], 1);
			$this->template_data=array('status'=>article_m::UNPUBLISHED);
			$this->article_m->update_row($response['data']['id'],$this->template_data);
			$this->session->set_flashdata('success', 'article unpublished successfully');
		}
		catch(Exception $e){
			$this->session->set_flashdata('error', 'article not unpublished '.$e->getMessage());
		}
		$this->controller_redirect();				
	}

	function delete($slug=NULL){
		try{
			if(!permission_permit(array('delete-article'))) $this->controller_redirect('Permissioin Denied');
			if(!$slug) throw new Exception('Invalid paramter');
			$response=$this->get($slug);
			if(!$response['success']) throw new Exception($response['data'], 1);
			$this->template_data=array('status'=>article_m::DELETED);
			$this->article_m->update_row($response['data']['id'],$this->template_data);
			$this->session->set_flashdata('success', 'article deleted successfully');
		}
		catch(Exception $e){
			$this->session->set_flashdata('error', 'article not deleted '.$e->getMessage());
		}
		$this->controller_redirect();				
	}

	function view($slug=NULL){
		try{
			if(!permission_permit(array('view-article'))) $this->controller_redirect('Permissioin Denied');
			if(!$slug) throw new Exception('Invalid paramter');
			$response=$this->get($slug);
			if(!$response['success']) throw new Exception($response['data'], 1);
			$this->breadcrumb->append_crumb('View','view');
			$this->template_data['row']=$response['data'];			
			$this->template_data['subview']=self::MODULE.'view';
			$this->load->view('admin/main_layout',$this->template_data);
		}
		catch(Exception $e){
			$this->session->set_flashdata('error', 'article not found '.$e->getMessage());
			$this->controller_redirect();				
		}
	}

	function modal(){
		$this->template_data['subview']=self::MODULE.'hello';
		$this->load->view('admin/modal_layout',$this->template_data);
	}

	function upload(){
		$config['upload_path'] = "./uploads/pics/articles/";
		$config['allowed_types'] = 'gif|jpg|png';
		$config['max_size']	= '1000';
		$config['max_width']  = '1024';
		$config['max_height']  = '768';
		$this->load->library('upload', $config);
		if ( ! $this->upload->do_upload('std_pic'))
		{
			$data['error']=$this->upload->display_errors();
			throw new Exception("Could not add article <hr/>".$data['error']);
		}
		else{
			$data['success'] = array('upload_data' => $this->upload->data());
		}
	}

	function set_upload_rule(){
		if (empty($_FILES['std_pic']['name']))
			$this->form_validation->set_rules('std_pic', 'Student Picture', 'trim|required|xss_clean');
	}

	function controller_redirect($msg=false){
		if($msg) $this->session->set_flashdata('error', $msg);
		$this->template_data['link']=base_url().self::MODULE;
		redirect($this->template_data['link']);				
	}

	function get($slug=FALSE){
		$response['success']=false;
		$response['data']='Error Processing Request';
		if(!$slug) return $response;
		$article=$this->article_m->read_row_by_slug($slug);
		if($article) {
			$response['success']=true;
			$response['data']=$article;
		}
		else{
			$response['data']='article not found';
		}
		return $response;
	}


}
?>