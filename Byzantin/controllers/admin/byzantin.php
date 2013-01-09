<?php if( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Module Admin controller
 *
 */
class Byzantin extends Module_Admin
{
	static private $magic_quotes_gpc = FALSE;

	static private $input_uniq_prefix = 'i_';
	static private $key_uniq_prefix = 'k_';

	static private $menu_items_codes = array('source', 'destination','file', 'translate');

	static private $allowed_upload_extension = array('php','html');

	/**
	 * Maximum length of form text input element.
	 * Text longer than this is entered in a textarea.
	 *
	 */
	static private $textarea_line_break = 60;
	static private $textarea_rows = 3;


	private $menu_items = array();

	private $lang_directories = array();

	private $lang_directory = '';

	private $translations = array();


	/**
	 * Constructor
	 *
	 * @access  public
	 * @return  void
	 */
	public function construct()
	{
		$this->load->helper(array('form', 'url', 'file' ));

		$this->load->model('byzantin_model', '', TRUE);

		// Lang directories
		$this->get_lang_directories();

		// Lang dir
		$this->lang_directory = APPPATH . 'language';

		require_once(APPPATH.'config/iso639_1.php');
		$this->template['iso639'] = $iso639_1;

		// Suhosin suhosin.post.max_vars check
		$max_input_vars = ini_get('max_input_vars');
		$post_max_vars = ini_get('suhosin.post.max_vars');
		$this->template['post_max_vars'] = ($max_input_vars < $post_max_vars) ? $max_input_vars : $post_max_vars;

		// Post input uniq prefix
		$this->template['input_uniq_prefix'] = self::$input_uniq_prefix;
		$this->template['key_uniq_prefix'] = self::$key_uniq_prefix;

		if (get_magic_quotes_gpc())
			self::$magic_quotes_gpc = TRUE;

		$this->build_menu_item();
	}


	/**
	 * Admin panel
	 * Called from the modules list.
	 *
	 * @access  public
	 * @return  parsed view
	 *
	 */
	public function index()
	{
		$this->set_active_menu_item('source');

		$this->output('admin/index');
	}


	public function get_step()
	{
		$step = $this->session->userdata('byzantin_step');

		if ( ! $step)
		{
			$step = 'source';
			$this->session->set_userdata('byzantin_step', $step);
		}

		$this->{'get_' . $step}();
	}


	public function get_source()
	{
		$this->template['languages'] = $this->byzantin_model->list_languages($this->lang_directory);
		$this->output('admin/step_source');
	}


	public function get_destination()
	{
		$source_lang = $this->input->post('source_lang');

		if ( ! $source_lang) $source_lang = $this->session->userdata('byzantin_source_lang');

		if ( ! $source_lang)
		{
			$this->get_source();
		}
		else
		{
			$this->template['source_lang'] = $source_lang;
			$this->template['languages'] = $this->byzantin_model->list_languages($this->lang_directory);

			// Set the step
			$this->session->set_userdata('byzantin_source_lang', $source_lang);
			$this->session->set_userdata('byzantin_step', 'destination');
			$this->output('admin/step_destination');
		}
	}


	/**
	 * Stores the destination language
	 * and gets eht file list
	 *
	 */
	public function get_file()
	{
		$source_lang = $this->session->userdata('byzantin_source_lang');
		$dest_lang = $this->input->post('dest_lang');

		if ( ! $dest_lang) $dest_lang = $this->session->userdata('byzantin_dest_lang');

		if ( ! $source_lang )
			$this->get_source();
		else
		{
			if ( ! $dest_lang)
				$this->get_destination();
			else
			{
				$this->template['source_lang'] = 	$source_lang;
				$this->template['dest_lang'] = 		$dest_lang;
				$this->template['languages'] = 		$this->byzantin_model->list_languages($this->lang_directory);
				$this->template['source_files'] = 	$this->byzantin_model->list_language_files($this->lang_directory, $source_lang);
				$this->template['dest_files'] = 	$this->byzantin_model->list_language_files($this->lang_directory, $dest_lang);

				// Set the step
				$this->session->set_userdata('byzantin_dest_lang', $dest_lang);
				$this->session->set_userdata('byzantin_step', 'file');
				$this->output('admin/step_file');
			}
		}
	}


	/**
	 * Gets the translations items
	 *
	 */
	public function get_translate()
	{
		$source_lang = $this->session->userdata('byzantin_source_lang');
		$dest_lang = $this->session->userdata('byzantin_dest_lang');

		$file = $this->input->post('file');

		if ( ! $source_lang )
			$this->get_source();
		else
		{
			if ( ! $dest_lang)
				$this->get_destination();
			else
			{
				if ( ! $file)
					$this->get_file();
				else
				{
					// Store file in Session
					$this->session->set_userdata('byzantin_file', $file);

					// Translation data
					$this->load_source_file_translations();
					$this->load_destination_file_translations();
					$this->translations = $this->byzantin_model->validate_translations($this->translations);
					$this->translations = $this->byzantin_model->unescape_translations($this->translations);

					// Template data
					$this->template['source_lang'] = $source_lang;
					$this->template['dest_lang'] = 	$dest_lang;
					$this->template['file'] = 	$file;

					// Hidden form inputs
					$this->template['hidden']['source_lang'] = $source_lang;
					$this->template['hidden']['dest_lang'] = $dest_lang;
					$this->template['hidden']['file'] = $file;

					$this->template['translations'] = $this->translations;
					$this->template['textarea_line_break'] = self::$textarea_line_break;
					$this->template['textarea_rows'] = self::$textarea_rows;

					$this->output('admin/step_translate');
				}
			}
		}
	}


	public function upload()
	{
		if ( ! empty($_FILES['files']['name']))
		{
			$errors = array();

			$this->load->library('upload');

			$upload_path = $this->lang_directory.'/'.$this->input->post('dest_lang').'/';

			if ( ! is_dir($upload_path))
				mkdir($upload_path, 0755, TRUE);

			$config = array(
				'allowed_types' => '*',
				'upload_path' => $upload_path,
				'overwrite' => TRUE,
				'max_size' => 0,
			);

			$files = $_FILES;
			$cpt = count($_FILES['files']['name']);

			for($i=0; $i<$cpt; $i++)
			{
				$extension = end(explode('.', $files['files']['name'][$i]));

				if (in_array($extension, self::$allowed_upload_extension))
				{
					$_FILES['userfile'] = array(
						'name' => $files['files']['name'][$i],
						'type' => $files['files']['type'][$i],
						'tmp_name' => $files['files']['tmp_name'][$i],
						'error' => $files['files']['error'][$i],
						'size' => $files['files']['size'][$i],
					);

					$this->upload->initialize($config);

					if ( ! $this->upload->do_upload())
					{
						$errors[] = $files['files']['name'][$i].'('.$files['files']['type'][$i].')';
					}
				}
			}

			if ( ! empty($errors))
			{
				log_message('error', 'Byzantin->upload() : Error uploading :' . implode(',', $errors));
				$this->error(lang('module_byzantin_upload_error_message'));
			}
			else
			{
				$this->success(lang('module_byzantin_upload_success_message'));
			}
		}
		else
		{
			$this->error(lang('module_byzantin_upload_error_no_file_message'));
		}
		$this->response();
	}


	public function save()
	{
		$source_lang = $this->input->post('source_lang');
		$dest_lang = $this->input->post('dest_lang');
		$file = $this->input->post('file');

		// Sources
		$this->load_source_file_translations();

		// Add Post data to translations
		$this->load_post_data();

		$this->translations = $this->byzantin_model->escape_translations($this->translations);

		$result = $this->byzantin_model->save_translation_file(
			$this->get_file_path($file, $source_lang),
			$this->get_file_path($file, $dest_lang),
			$this->translations
		);

		if ($result !== FALSE)
		{
			// Answer
			// $this->success(lang('module_byzantin_translation_file_saved'));

			$this->callback[] = array(
				'fn' => 'ION.HTML',
				'args' => array(
					admin_url() . 'module/byzantin/byzantin/get_translate',
					array('file' => $file),
					array('update' => 'byzantinContainer')
				)
			);
			$this->callback[] = array(
				'fn' => 'ION.notification',
				'args' => array('success', lang('module_byzantin_translation_file_saved'))
			);
		}
		else
		{
			log_message('error', 'Error during file save');
			$this->error(lang('module_byzantin_translation_file_not_saved'));
		}

		$this->response();
	}


	private function backcup_file($file, $dest_lang)
	{
		// Backup original file
		if ( $this->backupFlag && is_file( $this->slaveModulePath ) ) {
			$slaveModule =  $this->_load_module( $this->slaveModulePath );
			$fp = fopen( $this->slaveModulePath . '.' . date( 'Y-M-d-H-i-s' ) . '.bak', 'w' );
			fwrite( $fp, implode( $slaveModule ) );
			fclose( $fp );
		}

	}


	private function load_post_data()
	{
		$prefix_len = mb_strlen( self::$input_uniq_prefix );

		foreach ( $_POST as $post_key => $post_value )
		{
			if ( strncmp( self::$input_uniq_prefix, $post_key, $prefix_len ) === 0 )
			{

				$md5_key = mb_substr($post_key, $prefix_len);
				$post_md5_key = self::$key_uniq_prefix . self::$input_uniq_prefix . $md5_key;

				$key = isset($_POST[$post_md5_key]) ? $_POST[$post_md5_key] : NULL;

				if ( ! is_null($key))
				{
					if ( ! array_key_exists( $key, $this->translations ) ) {
						$this->translations[$key]['source'] = NULL;
					}

					$this->translations[$key]['destination'] = $post_value;
				}
				else
				{
					log_message('error', 'Error on : ' . $post_md5_key);
				}
			}
		}
	}


	private function get_file_path($file_name, $lang)
	{
		return $this->lang_directory.'/'.$lang.'/'.$file_name;
	}


	private function load_source_file_translations()
	{
		$file_name = $this->session->userdata('byzantin_file');
		$lang = $this->session->userdata('byzantin_source_lang');
		$file_path = $this->get_file_path($file_name, $lang);

		$this->translations = $this->byzantin_model->load_source_file_translations($file_path);
	}


	private function load_destination_file_translations()
	{
		$file_name = $this->session->userdata('byzantin_file');
		$lang = $this->session->userdata('byzantin_dest_lang');
		$file_path = $this->get_file_path($file_name, $lang);

		$this->translations = $this->byzantin_model->load_destination_file_translations($file_path, $this->translations);
	}


	private function build_menu_item()
	{
		foreach(self::$menu_items_codes as $item)
		{
			$this->menu_items[] = array(
				'title' => lang('module_byzantin_menu_' . $item),
				'code' => $item,
				'active' => NULL
			);
		}
	}


	private function set_active_menu_item($key)
	{
		foreach($this->menu_items as $i => $item)
		{
			$this->menu_items[$i]['active'] = NULL;
			if ($item['code'] == $key)
			{
				$this->menu_items[$i]['active'] = 'active';
			}
		}
		$this->template['menu'] = $this->menu_items;
	}


	private function get_lang_directories()
	{
		$this->lang_directories = array(APPPATH . 'language' );

		$modules = array();

		include APPPATH . 'config/modules.php';

		foreach($modules as $folder)
		{
			$this->lang_directories[] = MODPATH . $folder . '/language';
		}

	}
}