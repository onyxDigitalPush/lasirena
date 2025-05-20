<?php

namespace App\Models\MainApp;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $primaryKey = 'product_id';

    protected $dates = ['start_date', 'end_date', 'start_sap', 'end_sap'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */


    protected $fillable = [
        'email_id',
        'sales',
        'family',
        'provider_cod',
        'provider_name',
        'product_cod',
        'product_description',
        'dto',
        'start_date',
        'end_date',
        'start_sap',
        'end_sap',
        'prevision',
        'language',
        'email',
        'redemption'

    ];

    public function store_product($data, $email_id)
    {

        Product::create([
            'email_id' => $email_id,
            'sales' => 0,
            'family' => $data->family,
            'provider_cod' => $data->cod_prov,
            'provider_name' => $data->provider_name,
            'product_cod' => $data->prod,
            'product_description' => $data->product_description,
            'dto' => $data->dto,
            'start_date' => Carbon::parse($data->start_date)->format('Y-m-d H:i:s'),
            'end_date' =>  Carbon::parse($data->end_date)->format('Y-m-d H:i:s'),
            'start_sap' => $data->start_sap != '' ? Carbon::parse($data->start_sap)->format('Y-m-d H:i:s') : null,
            'end_sap' => $data->end_sap != '' ? Carbon::parse($data->end_sap)->format('Y-m-d H:i:s') : null,
            'prevision' => $data->prevision,
            'language' => $data->language,
            'email' => $data->email,
            'redemption' => $data->redemption == 'true' ? 1 : 0,
            
        ]);
    }

    public function get_email_products_grouped_by_dates($email_id)
    {
        $array_product = Product::where('email_id', $email_id)->get();
        $array_gropued_product_by_dates = [];

        foreach ($array_product as $product)
        {
            $key = Carbon::parse($product->start_date)->format('Y_m_d') . '_' .  Carbon::parse($product->end_date)->format('Y_m_d');
            if (array_key_exists($key, $array_gropued_product_by_dates))
            {
                array_push($array_gropued_product_by_dates[$key], $product);
            }
            else
            {
                $array_gropued_product_by_dates[$key] = array();
                array_push($array_gropued_product_by_dates[$key], $product);
            }
            //array_push($array_gropued_product_by_dates[$key], $product);
        }
        return $array_gropued_product_by_dates;
    }
}
