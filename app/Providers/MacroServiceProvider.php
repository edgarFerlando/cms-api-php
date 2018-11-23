<?php namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Form;
use App\Classes\CKEditor;
use Config;
use LaravelLocalization;
use Illuminate\Routing\UrlGenerator;

class MacroServiceProvider extends ServiceProvider  {

	protected $ckeditor_basePath = '/backend/plugins/ckeditor/';

	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		Form::macro('lang_text', function($name, $value = null, $options = array(), $errors = array()) {
			$default_locale = Config::get('app.locale');
		    $html = '<ul class="nav nav-pills lang-tab hide">';
		    $langs = LaravelLocalization::getSupportedLocales();
		    foreach ($langs as $locale => $properties) {
		        $is_active = $default_locale == $locale ? 'active' : '';
		        $html .= '<li class="' . $is_active . '"><a href="#" class="lang-' . $locale . '">' . strtoupper($locale) . '</a></li>';
		    }
		    $html .='</ul>';
		    $has_errors = lang_errors_has($errors, $name);
		    foreach ($langs as $locale => $properties) {
		        $is_hidden = $default_locale == $locale ? '' : 'hide';
		        $lang_value = isset($value[$locale]) ? $value[$locale] : '';
		        $html .= '<div class="lang-' . $locale . ' ' . $is_hidden . ' '.(isset($has_errors[$locale])?'':'validated').'">';
		        $html .= Form::text($name . '[' . $locale . ']', $lang_value, $options);
		        $html .= lang_errors($errors, $name.'.'.$locale);
		        $html .= '</div>';
		    }
		    return $html;
		});

	Form::macro('lang_textarea', function($name, $value = null, $options = array(), $errors = array()) {
	    $default_locale = Config::get('app.locale');
	    $html = '<ul class="nav nav-pills lang-tab hide">';
	    $langs = LaravelLocalization::getSupportedLocales();
	    foreach ($langs as $locale => $properties) {
	        $is_active = $default_locale == $locale ? 'active' : '';
	        $html .= '<li class="' . $is_active . '"><a href="#" class="lang-' . $locale . '">' . strtoupper($locale) . '</a></li>';
	    }
	    $html .='</ul>';
	    $has_errors = lang_errors_has($errors, $name);
		foreach ($langs as $locale => $properties) {
		        $is_hidden = $default_locale == $locale ? '' : 'hide';
		        $lang_value = isset($value[$locale]) ? $value[$locale] : '';
		        $html .= '<div class="lang-' . $locale . ' ' . $is_hidden . ' '.(isset($has_errors[$locale])?'':'validated').'">';
		        $html .= Form::textarea($name . '[' . $locale . ']', $lang_value, $options);
		        $html .= lang_errors($errors, $name.'.'.$locale);
		        $html .= '</div>';
		    }
	    return $html;
	});

	Form::macro('ckeditor', function($name, $value = null, $options = array()) {
	    $options['height'] = isset($options['height']) ? $options['height'] : 300;
	    $ckeditor = new CKEditor();
	    $ckeditor->basePath = url('/backend/plugins/ckeditor')."/";
	    $ckeditor->config['height'] = $options['height'];
	    $ckeditor->returnOutput = true;
	    $config['toolbar'] = array(
	        array('Format', 'Source', 'Maximize'),
	        array('Bold', 'Italic', 'Underline', 'Strike', 'RemoveFormat'),
	        array('NumberedList', 'BulletedList', 'Blockquote', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'),
	        array('Link', 'Unlink'),
	        array('Image', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar')
	    );
	    $config['filebrowserBrowseUrl'] = url(getLang() . '/admin/filemanager/show');
	    return $ckeditor->editor($name, $value, $config);
	});

	Form::macro('lang_ckeditor', function($name, $value = null, $options = array(), $errors = array()) {
		//table lang buang saja, ikut ke config mcamara saja, karena complete
	    $default_locale = Config::get('app.locale');
	    $html = '<ul class="nav nav-pills lang-tab hide">';
	    $langs = LaravelLocalization::getSupportedLocales();
	    foreach ($langs as $locale => $properties) {
	        $is_active = $default_locale == $locale ? 'active' : '';
	        $html .= '<li class="' . $is_active . '"><a href="#" class="lang-' . $locale . '">' . strtoupper($locale) . '</a></li>';
	    }
	    $html .='</ul>';
	    $has_errors = lang_errors_has($errors, $name);
	    foreach ($langs as $locale => $properties) {
	        $is_hidden = $default_locale == $locale ? '' : 'hide';
	        $lang_value = isset($value[$locale]) ? $value[$locale] : '';
	        $html .= '<div class="lang-' . $locale . ' ' . $is_hidden . ' '.(isset($has_errors[$locale])?'':'validated').'" >';
	        $html .= Form::ckeditor($name . '[' . $locale . ']', $lang_value, $options);
	        $html .= lang_errors($errors, $name.'.'.$locale);
	        $html .= '</div>';
	    }
	    return $html;
	});

	Form::macro('cke_min', function($name, $value = null, $options = array()) {
	    $options['height'] = isset($options['height']) ? $options['height'] : 300;
	    $ckeditor = new CKEditor();
	    $ckeditor->basePath = '/js/ckeditor/';
	    $ckeditor->config['height'] = $options['height'];
	    $ckeditor->returnOutput = true;
	    $config['toolbar'] = array(
	        array('Bold', 'Italic', 'Underline', 'Strike', 'RemoveFormat'),
	        array('Link', 'Unlink'),
	    );
	    $config['filebrowserBrowseUrl'] = url(getLang() . '/admin/filemanager/show');
	    return $ckeditor->editor($name, $value, $config);
	});

	Form::macro('cke_list', function($name, $value = null, $options = array()) {
	    $options['height'] = isset($options['height']) ? $options['height'] : 300;
	    $ckeditor = new CKEditor();
	    $ckeditor->basePath = '/js/ckeditor/';
	    $ckeditor->config['height'] = $options['height'];
	    $ckeditor->returnOutput = true;
	    $config['toolbar'] = array(
	    	array('Source', 'Maximize'),
	        array('Bold', 'Italic', 'Underline', 'Strike', 'RemoveFormat'),
	        array('BulletedList')
	    );
	    if(isset($options['class']))
	    	$config['class'] = $options['class'];
	    $config['filebrowserBrowseUrl'] = url(getLang() . '/admin/filemanager/show');
	    return $ckeditor->editor($name, $value, $config);
	});


	Form::macro('cke_image', function($name, $value = null, $options = array()) {
	    $options['height'] = isset($options['height']) ? $options['height'] : 300;
	    $ckeditor = new CKEditor();
	    $ckeditor->basePath = '/backend/plugins/ckeditor/';
	    $ckeditor->config['height'] = $options['height'];
	    $ckeditor->returnOutput = true;
	    $config['toolbar'] = array(
	        array('Image')
	    );
	    if(isset($options['class']))
	    	$config['class'] = $options['class'];
	    $config['filebrowserBrowseUrl'] = url(getLang() . '/admin/filemanager/show');
	    $value = $value != '' ? '<img src="'.url($value).'" />' : '';
	    return $ckeditor->editor($name, $value, $config);
	});

	/*Form::macro('cke_image', function($name, $value = null, $options = array()) {
	    $options['height'] = isset($options['height']) ? $options['height'] : 300;
	    $ckeditor = new CKEditor();
	    $ckeditor->basePath = '/js/ckeditor/';
	    $ckeditor->config['height'] = $options['height'];
	    $ckeditor->returnOutput = true;
	    $config['toolbar'] = array(
	        array('Image')
	    );
	    $config['filebrowserBrowseUrl'] = url(getLang() . '/admin/filemanager/show');
	    return $ckeditor->editor($name, $value, $config);
	});*/

	Form::macro('lang_cke_list', function($name, $value = null, $options = array(), $errors = array()) {
	    /*$default_locale = Config::get('app.locale');
	    $html = '<ul class="nav nav-pills lang-tab hide">';
	    $langs = Language::all();
	    foreach ($langs as $lang) {
	        $is_active = $default_locale == $lang->locale ? 'active' : '';
	        $html .= '<li class="' . $is_active . '"><a href="#" class="lang-' . $lang->locale . '">' . strtoupper($lang->locale) . '</a></li>';
	    }
	    $html .='</ul>';
	    foreach ($langs as $lang) {
	        $is_hidden = $default_locale == $lang->locale ? '' : 'hide';
	        $lang_value = isset($value[$lang->locale]) ? $value[$lang->locale] : '';
	        $html .= '<div class="lang-' . $lang->locale . ' ' . $is_hidden . '" >' . Form::cke_list($name . '[' . $lang->locale . ']', $lang_value, $options) . '</div>';
	    }
	    return $html;*/
	    $default_locale = Config::get('app.locale');
	    $html = '<ul class="nav nav-pills lang-tab hide">';
	    $langs = LaravelLocalization::getSupportedLocales();
	    foreach ($langs as $locale => $properties) {
	        $is_active = $default_locale == $locale ? 'active' : '';
	        $html .= '<li class="' . $is_active . '"><a href="#" class="lang-' . $locale . '">' . strtoupper($locale) . '</a></li>';
	    }
	    $html .='</ul>';
	    $has_errors = lang_errors_has($errors, $name);
	    foreach ($langs as $locale => $properties) {
	        $is_hidden = $default_locale == $locale ? '' : 'hide';
	        $lang_value = isset($value[$locale]) ? $value[$locale] : '';
	        $html .= '<div class="lang-' . $locale . ' ' . $is_hidden . ' '.(isset($has_errors[$locale])?'':'validated').'" >';
	        $html .= Form::cke_list($name . '[' . $locale . ']', $lang_value, $options);
	        $html .= lang_errors($errors, $name.'.'.$locale);
	        $html .= '</div>';
	    }
	    return $html;
	});

	Form::macro('lang_cke_min', function($name, $value = null, $options = array(), $errors = array()) {
	    /*$default_locale = Config::get('app.locale');
	    $html = '<ul class="nav nav-pills lang-tab hide">';
	    $langs = Language::all();
	    foreach ($langs as $lang) {
	        $is_active = $default_locale == $lang->locale ? 'active' : '';
	        $html .= '<li class="' . $is_active . '"><a href="#" class="lang-' . $lang->locale . '">' . strtoupper($lang->locale) . '</a></li>';
	    }
	    $html .='</ul>';
	    foreach ($langs as $lang) {
	        $is_hidden = $default_locale == $lang->locale ? '' : 'hide';
	        $lang_value = isset($value[$lang->locale]) ? $value[$lang->locale] : '';
	        $html .= '<div class="lang-' . $lang->locale . ' ' . $is_hidden . '" >' . Form::cke_min($name . '[' . $lang->locale . ']', $lang_value, $options) . '</div>';
	    }
	    return $html;*/
	    $default_locale = Config::get('app.locale');
	    $html = '<ul class="nav nav-pills lang-tab hide">';
	    $langs = LaravelLocalization::getSupportedLocales();
	    foreach ($langs as $locale => $properties) {
	        $is_active = $default_locale == $locale ? 'active' : '';
	        $html .= '<li class="' . $is_active . '"><a href="#" class="lang-' . $locale . '">' . strtoupper($locale) . '</a></li>';
	    }
	    $html .='</ul>';
	    $has_errors = lang_errors_has($errors, $name);
	    foreach ($langs as $locale => $properties) {
	        $is_hidden = $default_locale == $locale ? '' : 'hide';
	        $lang_value = isset($value[$locale]) ? $value[$locale] : '';
	        $html .= '<div class="lang-' . $locale . ' ' . $is_hidden . ' '.(isset($has_errors[$locale])?'':'validated').'" >';
	        $html .= Form::cke_min($name . '[' . $locale . ']', $lang_value, $options);
	        $html .= lang_errors($errors, $name.'.'.$locale);
	        $html .= '</div>';
	    }
	    return $html;
	});

	Form::macro('lang_cke_image', function($name, $value = null, $options = array(), $errors = array()) {
	    $default_locale = Config::get('app.locale');
	    $html = '<ul class="nav nav-pills lang-tab hide">';
	    $langs = LaravelLocalization::getSupportedLocales();
	    foreach ($langs as $locale => $properties) {
	        $is_active = $default_locale == $locale ? 'active' : '';
	        $html .= '<li class="' . $is_active . '"><a href="#" class="lang-' . $locale . '">' . strtoupper($locale) . '</a></li>';
	    }
	    $html .='</ul>';
	    $has_errors = lang_errors_has($errors, $name);
	    foreach ($langs as $locale => $properties) {
	        $is_hidden = $default_locale == $locale ? '' : 'hide';
	        //$lang_value = isset($value[$locale]) && $value[$locale] != '' ? '<img src="'.url($value[$locale]).'" />' : '';
	        $lang_value = isset($value[$locale]) && $value[$locale] != '' ? $value[$locale].'" />' : '';
	        $html .= '<div class="lang-' . $locale . ' ' . $is_hidden . ' '.(isset($has_errors[$locale])?'':'validated').'" >';
	        $html .= Form::cke_image($name . '[' . $locale . ']', $lang_value, $options);
	        $html .= lang_errors($errors, $name.'.'.$locale);
	        $html .= '</div>';


	        //$html .= '<div class="lang-' . $lang->locale . ' ' . $is_hidden . '" >' . Form::cke_image($name . '[' . $lang->locale . ']', $lang_value, $options) . '</div>';
	    }
	    return $html;
	});


		Form::macro(
	    	'selectWithDisabled',
		    function($name, $options = [], $selected = null, $attributes = [], $disabled = [])
		    {
		        $html = '<select name="' . $name . '"';
		        foreach ($attributes as $attribute => $value)
		        {
		            $html .= ' ' . $attribute . '="' . $value . '"';
		        }
		        $html .= '>';
		        
		         foreach ($options as $value => $text)
		        {
		            $html .= '<option value="' . $value . '"' .
		                ($value == $selected ? ' selected="selected"' : '') .
		                (in_array($value, $disabled) ? ' disabled="disabled"' : '') . '>' .
		                $text . '</option>';
		        }
		 
		        $html .= '</select>';
		 
		        return $html;
		    }
		);

	
	}

	/**
	 * Register the application services.
	 *
	 * @return void
	 */
	public function register()
	{
		
	}

}
