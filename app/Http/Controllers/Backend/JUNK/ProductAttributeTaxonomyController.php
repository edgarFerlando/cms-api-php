<?php namespace App\Http\Controllers\Backend;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\ProductAttributeTaxonomy;
use Input;
use Response;

class ProductAttributeTaxonomyController extends Controller {

	public function postFindByTaxonomyId(){
        $id = Input::get('id');
        $taxos = ProductAttributeTaxonomy::with('productAttribute.productAttributeOption.productAttributeOptionTranslation')->where('taxonomy_id', $id)->get();
      
        $ff_name = [];
        $prod_attr_options = [];
        $ff = '';
        foreach($taxos as $prod_taxo){
        	$ff_variant_name = 'variant['.$prod_taxo->productAttribute->id.']';
        	$ff_name[] = $ff_variant_name;

        	$attr_options[''] = '-';
        	foreach($prod_taxo->productAttribute->productAttributeOption as $prod_attr_opt){
        		$attr_options[$prod_attr_opt->id] = $prod_attr_opt->productAttributeOptionTranslation->name;
	        	//$prod_attr_options[$prod_taxo->productAttribute->id][$prod_attr_opt->id] = $prod_attr_opt->productAttributeOptionTranslation->name;
	        }
	        Form::label('variant', $attribute_option->name);
	        Form::select($ff_variant_name, $attr_options, '')
	        <span class="help-block"></span>
        }

        //build form
        <div class="col-lg-3">
			<div class="form-group">
				{!! Form::label('variant', $attribute_option->name) !!}
				<?php
				$th_variant[] = '<th>'.$attribute_option->name.'</th>';
				$attr_options = [];
				$attr_options[''] = '-';
				$attr_options += $attribute_option->productAttributeOption->lists('name', 'id');
				$ff_variant_name = 'variant['.$attribute_option->id.']';
				$ff_variants[] = $ff_variant_name;
				?>
				{!! Form::select($ff_variant_name, $attr_options, '') !!}
				<span class="help-block"></span>
			</div>
		</div>

        return Response::json([
        	'ff_name' => $ff_name,
        	'prod_attr_options' => $prod_attr_options
        	]);
    }

}
