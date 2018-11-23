<?php namespace App\Repositories\BankStatementMutation;

use App\Models\BankStatement;
use App\Repositories\RepositoryAbstract;
use App\Repositories\CrudableInterface as CrudableInterface;
use App\Repositories\RepositoryInterface as RepositoryInterface;
use Schema;

class BankStatementMutationRepository extends RepositoryAbstract {
    
    public function model()
    {
        return 'App\Models\BankStatement';
    }

    public function search($input)
    {
        $query = BankStatement::query();

        $columns = Schema::getColumnListing('bank_statements');
        $attributes = array();

        foreach($columns as $attribute)
        {
            $attributes[$attribute] =  null;
            if(isset($input[$attribute]) and !empty($input[$attribute]))
            {
                $query->where($attribute, $input[$attribute]);
                $attributes[$attribute] = $input[$attribute];
            }
        }

        //query period_date
        if (isset($input['period']) && !empty($input['period']))
        {
            $query->whereRaw("to_char(transaction_date, 'YYYY-MM') = trim('".$input['period']."')");
        }

        /*
        ** Filter
        */
        $this->filter($input, $query);
        
        /*
        ** Get count
        */
        $total = $query->count();

        /*
        ** Pagination
        */
        $this->pagination($input, $query);

        /*
        ** Order
        */
        $this->order($input, $query);

        return [$query->get(), $attributes, 'total'=>$total];
    }

    public function lastUpdated()
    {
        $query = BankStatement::orderBy('updated', 'DESC')->first();
        if($query)
        {
            return $query->updated_at->format('Y-m-d H:i:s');
        }

        return date("Y-m-d H:i:s");
    }

    private function filter($input, &$query)
    {
        if(isset($input['filter']))
        {
            $filters = json_decode($input['filter']);

            if(count($filters))
            {
                foreach ($filters as $filter) {
                    switch ($filter->operator) {
                        case 'like':
                            $query->where($filter->field, $filter->operator, '%'.$filter->value.'%');
                        break;

                        case 'between':
                            $query->whereBetween($filter->field, [$filter->value[0], $filter->value[1]]);
                        break;

                        case 'notbetween':
                            $query->whereNotBetween($filter->field, [$filter->value[0], $filter->value[1]]);
                        break;

                        case 'in':
                            $query->whereIn($filter->field, $filter->value);
                        break;

                        case 'notin':
                            $query->whereNotIn($filter->field, $filter->value);
                        break;
                        
                        default:
                            $query->where($filter->field, $filter->operator, $filter->value);
                        break;
                    }
                }
            }
        }
    }

    private function pagination($input, &$query)
    {
        if(isset($input['offset']) && $input['offset'] > 0)
        {
            $query->skip($input['offset']);
        }

        if(isset($input['perpage']) && $input['perpage'] > 0)
        {
            $query->take($input['perpage']);
        }
    }

    private function order($input, &$query)
    {
        if(isset($input['order']))
        {
            $orders = json_decode($input['order']);

            if(count($orders))
            {
                foreach ($orders as $order) {
                    $query->orderBy($order->field, $order->sort);
                }
            }
        }
    }

}