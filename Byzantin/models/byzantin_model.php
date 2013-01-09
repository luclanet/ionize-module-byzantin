<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Byzantin module Model
 * To avoid models collision, the models should be named like this :
 * <Module>_<Model name>_model
 *
 */

class Byzantin_model extends Base_model
{
	/**
	 * Model Constructor
	 *
	 * @access	public
	 */
	public function __construct()
	{
		parent::__construct();
	}


	public function save_translation_file($source_path, $destination_path, $translations)
	{
		$result = FALSE;

		// Load the source file
		$source =  $this->load_file_content($source_path);

		if ($source)
		{
			// Remove closing PHP tag if it exists - allows for easy addition of additonal lines
			if ( $source && mb_strpos( $source[ count( $source ) - 1 ], '?>' ) !== FALSE ) {
				unset( $source[ count( $source ) - 1 ] );
			}

			// Replace sources translations with new destination translations (including duplicates)
			foreach ($source as $line_number => $line)
			{
				if ($this->is_lang_key( $line ))
				{
					$key = $this->get_lang_key( $line );
					$translation = $this->get_lang_content($line, FALSE);
					$source[$line_number ]= str_replace( $translation, '"'.$this->escape_double_quotes( $translations[$key]['destination'] ).'"' , $source[$line_number] );
				}
			}

			// Delete translations common to both master and slave languages
			// Remainder will be vestigial slave language declarations
			foreach ($source as $line ) {
				if ( $this->is_lang_key($line)) {
					$key = $this->get_lang_key($line);
					unset( $translations[$key] );
				}
			}

			// Append any unmatched translations originally in the slave file
			if ( count($translations) )
			{
				// Add some padding
				$source[] = NULL;

				foreach ( $translations as $key => $translation ) {
					$source[] = '$lang[\'' . $key . '\'] = "' . $translation['destination'] . '";';
				}
			}

			// Clean up new line characters from textarea inputs
			foreach ( $source as $line_number => $line ) {
				$master[ $line_number ] = str_replace( "\n", '', $line );
				$master[ $line_number ] .= "\n";
			}


			// Check syntax and attempt to save file
			$php = implode( $source );
			if ( ! $this->invalid_php_syntax( $php ) )
			{
				$destination_dir = explode('/', $destination_path);
				array_pop($destination_dir);
				$destination_dir = implode('/', $destination_dir);
				if ( ! is_dir($destination_dir))
					mkdir($destination_dir, 0755, TRUE);

				$this->save_translation_file_backup($destination_path);


				$fp = @fopen( $destination_path, 'w' );
				if ( fwrite( $fp, $php ) !== FALSE )
				{
					fclose( $fp );
					unset( $_POST );

					foreach ( $source as $line ) {
						$result .= htmlspecialchars($line) . '<br />';
					}
				}
			}
			else
			{
				log_message('error', 'Byzantin_model->save_translation_file() : Invalid PHP Syntax in destination file');
				// log_message('error', print_r($source, true));
			}
		}

		return $result;
	}


	private function save_translation_file_backup($path)
	{
		$old_file = $this->load_file_content($path);

		$fp = fopen( $path . '.' . date( 'Ymd_His' ) . '.bak', 'w' );
		fwrite( $fp, implode( $old_file ) );
		fclose( $fp );
	}



	public function load_source_file_translations($file_path)
	{
		$translations = array();

		$file_content = $this->load_file_content($file_path);

		foreach ($file_content as $line_number => $line)
		{
			// Extract each key and value
			if ( $this->is_lang_key($line) )
			{
				$key = $this->get_lang_key($line);
				$translations[$key]['source'] = $this->get_lang_content($line);
				$translations[$key]['destination'] = NULL;
			}
		}
		return $translations;
	}


	public function load_destination_file_translations($file_path, $translations)
	{
		$file_content = $this->load_file_content($file_path);

		// File exists ?
		if ($file_content)
		{
			foreach ( $file_content as $line )
			{
				// Extract each key and value
				if ( $this->is_lang_key($line) )
				{
					$key = $this->get_lang_key( $line );
					if ( ! array_key_exists( $key, $translations ) )
						$translations[$key]['source'] = NULL;

					if ( ! array_key_exists( 'source', $translations[$key] ) )
						$translations[$key]['source'] = NULL;

					$translations[$key]['destination'] = $this->get_lang_content($line);
				}
			}
		}

		return $translations;
	}


	private function is_lang_key( $line )
	{
		$line = trim($line);
		if(empty($line) || mb_stripos( $line , '$lang[' ) === FALSE )
		{
			return FALSE;
		}
		return TRUE;
	}


	private function get_lang_key($line)
	{
		// Trim forward to the first quote mark
		$line = trim( mb_substr($line, mb_strpos($line, '[') + 1 ));

		// Trim forward to the second quote mark
		$line = trim( mb_substr($line, 0, mb_strpos($line, ']')));

		return mb_substr($line, 1, mb_strlen($line) - 2);
	}


	public function load_file_content($path)
	{
		$file = FALSE;

		if (is_file($path))
			$file = @file( $path );

		return $file;
	}

	/**
	 * Extract translation string from a line of PHP code
	 *
	 * @param $line string
	 * @return string
	 */
	public function get_lang_content($line, $with_trim = TRUE)
	{
		// Trim forward to the first quote mark
		$line = trim( mb_substr( $line, strpos( $line, '=' ) + 1 ) );

		// Trim backward from the semi-colon
		$line = mb_substr( $line, 0, mb_strrpos( $line, ';' ) );

		if ($with_trim)
		{
			$pattern = '/^[\'"]?(.*)[\'"]{1}$/';
			preg_match($pattern, $line, $matches);
			if ( count( $matches ) >= 1 ) {
				$line = $matches[ 1 ];
			}
		}
		$line = html_entity_decode($line, ENT_NOQUOTES, 'UTF-8');

		return $line;
	}



	public function validate_translations($translations)
	{
		foreach ($translations as $key => &$translation)
		{
			// Translation information
			$translation['note'] = NULL;
			$translation['error'] = $this->validate_line( $translation['destination'], $key );
			$translation['same'] = $translation['source'] == $translation['destination'] ? TRUE : FALSE;

			// Force blank translations to source value
			/*
			if ( mb_strlen( trim( $translation['destination'] ) ) == 0 )
				$translation['destination'] = $translation['source'];
			*/

			if ( ! $translation['source'] ) {
				$translation['note'] = "Mismatch - does not exist in master translation";
			}
		}

		return $translations;
	}

	public function unescape_translations($translations)
	{
		foreach ($translations as $key => &$translation)
		{
			$translation['destination'] = $this->unescape_double_quotes($translation['destination']);
			$translation['destination'] = $this->unescape_quotes($translation['destination']);
		}
		return $translations;
	}

	public function escape_translations($translations)
	{
		foreach ($translations as $key => &$translation)
		{
			$translation['destination'] = $this->escape_double_quotes($translation['destination']);
		}
		return $translations;
	}

	private function escape_double_quotes($line)
	{
		$line = str_replace( '\"', '"', $line );
		$line = str_replace( '"', '\"', $line );
		return $line;
	}

	private function unescape_double_quotes($line)
	{
		$line = str_replace( '\"', '"', $line );
		return $line;
	}

	private function escape_quotes($line)
	{
		$line = str_replace( "\'", "'", $line );
		$line = str_replace( "'", "\'", $line );
		return $line;
	}

	private function unescape_quotes($line)
	{
		$line = str_replace( "\'", "'", $line );
		return $line;
	}


	public function validate_line($line, $key=NULL)
	{
		if ( $this->invalid_quotation_marks($line) )
		{
			// $this->validated = FALSE;
			return  "Invalid syntax - check for unbalanced quotation marks";
		}

		// Blank ?
		/*
		if ( mb_strlen( trim( $line ) ) == 0 )
		{
			// $this->validated = FALSE;
			return  "Entry cannot be blank. Default set to source translation.";
		}
		*/

		// PHP check
		if ( $this->invalid_php_translation( $line ) )
		{
log_message('error', $line);
			// $this->validated = FALSE;
			return  "Invalid PHP syntax ";
		}

		return NULL;
	}


	protected function invalid_quotation_marks( $line )
	{
		/* TODO - pure regex version */
		// Strip escaped quote marks
		$line = str_replace( "\'", '', $line );
		$line = str_replace( '\"', '', $line );

		$line = str_replace( '"', '', $line );
		$line = str_replace( "'", '', $line );

		// Remove text enclosed by paired quotation marks
		$line = preg_replace( '/[\']{1}[^\']*[\']|["]{1}[^"]*["]/', '', $line );

		// Return failed result if any quotation marks remain
		if ( mb_strpos( $line, '\'' ) !== FALSE || mb_strpos( $line, '"' ) !== FALSE )
			return TRUE;

		return FALSE;
	}


	protected function invalid_php_translation( $line, &$err = '', &$bad_code = '' ) {

		// Insert translation into a dummy php string
		$line = $this->escape_double_quotes($line);
		$line = '$dummy_variable = "' . $line . '";';

		return $this->invalid_php_syntax( $line, $err, $bad_code );
	}


	/**
	 * Check PHP syntax
	 *
	 * Returns FALSE if no errors found otherwise returns the line number of the
	 * error with the error message and bad code in variables passed by reference
	 *
	 * @param        $php
	 * @param string $err
	 * @param string $bad_code
	 *
	 * @return bool|int
	 */
	protected function invalid_php_syntax( $php, &$err = '', &$bad_code = '' )
	{
		// Remove opening and closing PHP tags
		$php = str_replace( '<?php', '', $php );
		$php = str_replace( '?>', '', $php );

		// Evaluate the code
		ob_start();
		eval( $php );
		$err = ob_get_contents();
		ob_end_clean();

		if( ! empty($err) )
		{
			// TODO : Catch the error line and return it
			// -> Highlight the error line on frontend
			// log_message('error', print_r($err, true));
			if ( mb_stripos( $err, 'Parse error' ) == FALSE )
				return FALSE;
		}

		// Remove any html tags returned in error message
		$err_text = strip_tags( $err );

		// Get the line number
		$line = (int) trim( substr( $err_text, strripos( $err_text, ' ' ) ) );

		$php = explode( "\n", $php );

		$bad_code = $php[ max( 0, $line - 1 ) ];

		return $line;
	}




	public function list_languages($dir)
	{
		$languages = array();

		$d = @dir( $dir );
		if ( $d )
		{
			while (FALSE !== ($entry = $d->read()))
			{
				if ( ( substr($entry, 0, 1) != '.' )  && ( $entry != 'CVS' ) && is_dir( $dir . '/' . $entry) )
				{
					$d2 = @dir( $dir.'/'.$entry );
					$existing_files = 0;
					while (($file = $d2->read()) !== FALSE)
					{
						if( pathinfo($dir.'/'.$entry.'/'.$file, PATHINFO_EXTENSION) == 'php' )
							$existing_files += 1;
					}
					$languages[$entry]['code'] = $entry;
					$languages[$entry]['existing_files'] = $existing_files;
				}
			}
			$d->close();
		}

		sort( $languages );

		return $languages;
	}


	/**
	 * List language files
	 *
	 * @param $language
	 *
	 * @return array|bool
	 *
	 */
	public function list_language_files($dir, $language )
	{
		$language_files = array();

		$dir = $dir . '/' . $language;

		if (is_dir($dir))
		{
			$d = @dir( $dir );

			if ( $d )
			{
				while (FALSE !== ($entry = $d->read()))
				{
					$file = $dir . '/' . $entry;
					if ( is_file( $file ) )
					{
						$path_parts = pathinfo( $file );
						if ( $path_parts['extension'] == 'php' )
						{
							$nb_items = 0;
							$file_content = $this->load_file_content($file);

							foreach ( $file_content as $line )
							{
								// Extract each key and value
								if ( $this->is_lang_key($line) && $this->get_lang_content($line) != '')
									$nb_items += 1;
							}

							$language_files[$entry] = array(
								'name' => $entry,
								'nb_items' => $nb_items
							);
						}
					}
				}
				$d->close();
			}

			asort( $language_files );
		}

		return $language_files;
	}

}

