<?php namespace App\Http\Controllers\Backend;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class ReportController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		//

		$dataRegister = \DB::select("
			select distinct a.email from users a inner join role_user b ON a.id = b.user_id
			where 
				b.role_id = '7'
		");

		$countDataRegister = count($dataRegister);
	
		$dataRegisterActive = \DB::select("
			select distinct a.email from users a inner join role_user b ON a.id = b.user_id
			where 
				b.role_id = '7'
				and a.is_active in (1,2)
		");

		$countDataRegisterActive = count($dataRegisterActive);
	
		$dataConsulPeople = \DB::select("
			select distinct b.email
			from cfp_schedules a INNER JOIN users b ON a.client_id = b.id
		");

		$countDataConsulPeople = count($dataConsulPeople);

		$dataConsul = \DB::select("
			select * from cfp_schedules a INNER JOIN users b ON a.client_id = b.id
		");

		$countDataConsul = count($dataConsul);

		$dataConsulNext = \DB::select("
		select * from cfp_schedules a INNER JOIN role_user b ON a.client_id = b.user_id 
		where a.schedule_start_date > date_trunc('day', now())
		");

		$countDataConsulNext = count($dataConsulNext);


		$dataActiveWallet = \DB::select("
		select count(*) as tbl from (
			select distinct user_id from bank_accounts
		) as tbl
		");

		$countDataActiveWallet = $dataActiveWallet[0]->tbl; //count($dataActiveWallet);


		$dataRegisterToday = \DB::select("
			select distinct a.email from users a inner join role_user b ON a.id = b.user_id
			where 
				b.role_id = '7'
				and a.created_at > date_trunc('day', now())
		");

		$countDataRegisterToday = count($dataRegisterToday);



		$dataRegisterYesterday = \DB::select("
			select distinct a.email from users a inner join role_user b ON a.id = b.user_id
			where 
				b.role_id = '7'
				and a.created_at > date_trunc('day', date_trunc('day', now()) - interval '1 day')
				and a.created_at < date_trunc('day', now())
		");

		$countDataRegisterYesterday = count($dataRegisterYesterday);













		$dataAssigns = \DB::select("
		select
			case 
			when z.ket = 'CFP dari Reference Code ' then 'Reference Code yang dimasukan salah, sehingga Assign by System'
			when z.ket is null then 'Belum di assign'
			else 
				z.ket
			end
			, z.jumlah
		from
		(
		select 
			substring(c.notes from 1 for 24) ket
			, count(*) jumlah
		from 
			users a inner join role_user b ON a.id = b.user_id
			LEFT JOIN cfp_clients c ON a.id = c.client_id
		where 
			b.role_id = '7'
		group by substring(c.notes from 1 for 24)
		order by substring(c.notes from 1 for 24)
		) z
		");


		return view('backend.report.index', compact( 
			'countDataRegister',
			'countDataRegisterActive',
			'countDataConsulPeople',
			'countDataConsul',
			'countDataConsulNext',
			'countDataActiveWallet',
			'countDataRegisterToday',
			'countDataRegisterYesterday',
			'dataAssigns'
		));

	}



	/**
	 | ----------------------------------------
	 | 
	 |
	 */

	public function getByDate($start, $end)
	{
		/**
		 * Register
		 */

		$dataRegister = \DB::select("
			select distinct a.email from users a inner join role_user b ON a.id = b.user_id
			where 
				b.role_id = '7'
				and a.created_at BETWEEN '".$start."' AND '".$end."'
		");
		$countDataRegister = count($dataRegister);
	
		$dataRegisterToday = \DB::select("
			select distinct a.email from users a inner join role_user b ON a.id = b.user_id
			where 
				b.role_id = '7'
				and a.created_at > date_trunc('day', now())
		");
		$countDataRegisterToday = count($dataRegisterToday);

		$dataRegisterYesterday = \DB::select("
			select distinct a.email from users a inner join role_user b ON a.id = b.user_id
			where 
				b.role_id = '7'
				and a.created_at > date_trunc('day', date_trunc('day', now()) - interval '1 day')
				and a.created_at < date_trunc('day', now())
		");
		$countDataRegisterYesterday = count($dataRegisterYesterday);


		/**
		 * Assign notes ...
		 */

		$dataAssigns = \DB::select("
		select
			case 
			when z.ket = 'CFP dari Reference Code ' then 'Reference Code yang dimasukan salah, sehingga Assign by System'
			when z.ket is null then 'Belum di assign'
			else 
				z.ket
			end
			, z.jumlah
		from
		(
		select 
			substring(c.notes from 1 for 24) ket
			, count(*) jumlah
		from 
			users a inner join role_user b ON a.id = b.user_id
			LEFT JOIN cfp_clients c ON a.id = c.client_id
		where 
			b.role_id = '7'
			and a.created_at BETWEEN '".$start."' AND '".$end."'
		group by substring(c.notes from 1 for 24)
		order by substring(c.notes from 1 for 24)
		) z
		");



		/**
		 * Active
		 */

		$dataRegisterActive = \DB::select("
			select distinct a.email from users a inner join role_user b ON a.id = b.user_id
			where 
				b.role_id = '7'
				and a.is_active in (1,2)
				and a.created_at BETWEEN '".$start."' AND '".$end."'
		");

		$countDataRegisterActive = count($dataRegisterActive);
	


		/**
		 * Consultation ...
		 */

		$dataConsulPeople = \DB::select("
			select distinct b.email
			from cfp_schedules a INNER JOIN users b ON a.client_id = b.id
			where 
				a.created_at BETWEEN '".$start."' AND '".$end."'
		");

		$countDataConsulPeople = count($dataConsulPeople);

		$dataConsul = \DB::select("
			select * from cfp_schedules a INNER JOIN users b ON a.client_id = b.id
			where 
				a.created_at BETWEEN '".$start."' AND '".$end."'
		");

		$countDataConsul = count($dataConsul);

		$dataConsulNext = \DB::select("
			select * from cfp_schedules a INNER JOIN role_user b ON a.client_id = b.user_id 
			where a.schedule_start_date > date_trunc('day', now())
		");

		$countDataConsulNext = count($dataConsulNext);


		$dataConsulPeopleAssigns = \DB::select("
		select z.ket, count(*) jumlah
		FROM (
		select distinct 
			b.email, 
			case 
			when substring(c.notes from 1 for 24) = 'CFP dari Reference Code ' then 'Reference Code yang dimasukan salah, sehingga Assign by System'
			when c.notes is null then 'Belum di assign'
			else 
				c.notes
			end ket
		from 
			cfp_schedules a INNER JOIN users b ON a.client_id = b.id
			LEFT JOIN cfp_clients c ON a.client_id = c.client_id
		where 
			a.created_at BETWEEN '".$start."' AND '".$end."'
		) z
		GROUP BY z.ket
		");



		$dataCFPScheduleConsults = \DB::select("
		select 
			a.name,
			(
				select count(distinct d.client_id) from cfp_clients d 
				where a.id = d.cfp_id
					and d.client_id in (
						select e.client_id from cfp_schedules e
					)
			) jumlah_client_schedule,
			(
				select count(distinct c.schedule_start_date) from cfp_schedules c where a.id = c.cfp_id
			) jumlah_schedule
		from users a inner join role_user b ON a.id = b.user_id
		where 
			b.role_id = '6'
		order by a.name
		");




		/**
		 * Wallet ...
		 */

		$dataActiveWallet = \DB::select("
			select distinct a.user_id, b.email from bank_statements a LEFT JOIN users b ON a.user_id = b.id
			where a.created_at BETWEEN '".$start."' AND '".$end."'
		");

		$countDataActiveWallet = count($dataActiveWallet);


		/**
		 * Wallet Reference Code
		 * Total user berdasarkan Assign ke CFP yang telah membuka Wallet
		 */

		$dataOpenWalletPeopleAssigns = \DB::select("
		select
			case 
			when z.ket = 'CFP dari Reference Code ' then 'Reference Code yang dimasukan salah, sehingga Assign by System'
			when z.ket is null then 'Belum di assign'
			else 
				z.ket
			end
			, z.jumlah
		from
		(
		select 
			substring(c.notes from 1 for 24) ket
			, count(*) jumlah
		from 
			users a inner join role_user b ON a.id = b.user_id
			LEFT JOIN cfp_clients c ON a.id = c.client_id
		where 
			b.role_id = '7'
			and a.id in (
				select d.user_id from bank_statements d
			)
			and a.created_at BETWEEN '".$start."' AND '".$end."'
		group by substring(c.notes from 1 for 24)
		order by substring(c.notes from 1 for 24)
		) z
		");



		
		/**
		 * Financial Check Up Reference Code
		 * Total user berdasarkan Assign ke CFP yang telah melakukan financial checkup
		 */

		$dataFincheckPeopleAssigns = \DB::select("
		select
			case 
			when z.ket = 'CFP dari Reference Code ' then 'Reference Code yang dimasukan salah, sehingga Assign by System'
			when z.ket is null then 'Belum di assign'
			else 
				z.ket
			end
			, z.jumlah
		from
		(
		select 
			substring(c.notes from 1 for 24) ket
			, count(*) jumlah
		from 
			users a inner join role_user b ON a.id = b.user_id
			LEFT JOIN cfp_clients c ON a.id = c.client_id
		where 
			b.role_id = '7'
			and a.id in (
				select d.user_id from expenses d
			)
			and a.created_at BETWEEN '".$start."' AND '".$end."'
		group by substring(c.notes from 1 for 24)
		order by substring(c.notes from 1 for 24)
		) z
		");


		return view('backend.report.date', compact( 
			'countDataRegister',
			'countDataRegisterActive',
			'countDataConsulPeople',
			'countDataConsul',
			'countDataConsulNext',
			'countDataActiveWallet',
			'countDataRegisterToday',
			'countDataRegisterYesterday',
			'dataAssigns',
			'dataConsulPeopleAssigns',
			'dataFincheckPeopleAssigns',
			'dataOpenWalletPeopleAssigns',
			'dataCFPScheduleConsults'
		));
	}




	public function getByMonth($year, $month) {



		$last_date = 31;

		switch($month) {
			case "01" : $last_date = 31; break;
			case "02" : $last_date = 28; break;
			case "03" : $last_date = 31; break;
			case "04" : $last_date = 30; break;
			case "05" : $last_date = 31; break;
			case "06" : $last_date = 30; break;
			case "07" : $last_date = 31; break;
			case "08" : $last_date = 31; break;
			case "09" : $last_date = 30; break;
			case "10" : $last_date = 31; break;
			case "11" : $last_date = 30; break;
			case "12" : $last_date = 31; break;
		}


		$dataRegister = \DB::select("
			SELECT * FROM crosstab (
			  $$ 

				SELECT
					CASE 
						WHEN z.ket = 'CFP dari Reference Code ' THEN 'Reference Code yang dimasukan salah, sehingga Assign by System'
						WHEN z.ket IS NULL THEN 'Belum di assign'
					ELSE 
						z.ket
					END
					, EXTRACT(DAY FROM z.created_at) AS MONTH
					, z.jumlah
				FROM
				(
				SELECT 
					a.created_at::DATE
					, substring(c.notes FROM 1 FOR 24) ket
					, count(*) jumlah
				FROM 
					users a INNER JOIN role_user b ON a.id = b.user_id
					LEFT JOIN cfp_clients c ON a.id = c.client_id
				WHERE 
					b.role_id = '7'
					AND a.created_at::DATE BETWEEN '".$year."-".$month."-01' AND '".$year."-".$month."-".$last_date."'
				GROUP BY substring(c.notes FROM 1 FOR 24), a.created_at::DATE
				ORDER BY substring(c.notes FROM 1 FOR 24), a.created_at::DATE
				) z

			  $$,
			  $$ SELECT m FROM generate_series(1,31) m $$
			) AS (
			  	ket CHARACTER varying(255), 
			  	\"t_1\" character varying(10),
				\"t_2\" character varying(10),
				\"t_3\" character varying(10),
				\"t_4\" character varying(10),
				\"t_5\" character varying(10),
				\"t_6\" character varying(10),
				\"t_7\" character varying(10),
				\"t_8\" character varying(10),
				\"t_9\" character varying(10),
				\"t_10\" character varying(10),
				\"t_11\" character varying(10),
				\"t_12\" character varying(10),
				\"t_13\" character varying(10),
				\"t_14\" character varying(10),
				\"t_15\" character varying(10),
				\"t_16\" character varying(10),
				\"t_17\" character varying(10),
				\"t_18\" character varying(10),
				\"t_19\" character varying(10),
				\"t_20\" character varying(10),
				\"t_21\" character varying(10),
				\"t_22\" character varying(10),
				\"t_23\" character varying(10),
				\"t_24\" character varying(10),
				\"t_25\" character varying(10),
				\"t_26\" character varying(10),
				\"t_27\" character varying(10),
				\"t_28\" character varying(10),
				\"t_29\" character varying(10),
				\"t_30\" character varying(10),
				\"t_31\" character varying(10)
			);
		");


		


		$dataActive = \DB::select("
			SELECT * FROM crosstab (
			  $$ 

			select 
				'Active Email' ket,
				EXTRACT(DAY FROM a.created_at) as month,
				count(*) jumlah
			from users a inner join role_user b ON a.id = b.user_id
			where 
				b.role_id = '7'
				and a.is_active in (1,2)
				and a.created_at::date BETWEEN '".$year."-".$month."-01' AND '".$year."-".$month."-".$last_date."'
			group by EXTRACT(DAY FROM a.created_at)
			order by EXTRACT(DAY FROM a.created_at)
				
			$$,
			  $$ SELECT m FROM generate_series(1,31) m $$
			) AS (
			  	ket character varying(255), 
			  	\"t_1\" character varying(10),
				\"t_2\" character varying(10),
				\"t_3\" character varying(10),
				\"t_4\" character varying(10),
				\"t_5\" character varying(10),
				\"t_6\" character varying(10),
				\"t_7\" character varying(10),
				\"t_8\" character varying(10),
				\"t_9\" character varying(10),
				\"t_10\" character varying(10),
				\"t_11\" character varying(10),
				\"t_12\" character varying(10),
				\"t_13\" character varying(10),
				\"t_14\" character varying(10),
				\"t_15\" character varying(10),
				\"t_16\" character varying(10),
				\"t_17\" character varying(10),
				\"t_18\" character varying(10),
				\"t_19\" character varying(10),
				\"t_20\" character varying(10),
				\"t_21\" character varying(10),
				\"t_22\" character varying(10),
				\"t_23\" character varying(10),
				\"t_24\" character varying(10),
				\"t_25\" character varying(10),
				\"t_26\" character varying(10),
				\"t_27\" character varying(10),
				\"t_28\" character varying(10),
				\"t_29\" character varying(10),
				\"t_30\" character varying(10),
				\"t_31\" character varying(10)
			)
		");


		$dataConsultasionPerRefcode = \DB::select("
			SELECT * FROM crosstab (
			  $$ 

				select 
					z.ket, 
					EXTRACT(DAY FROM z.schedule_start_date) as month, 
					count(*) jumlah
				FROM (
				select distinct 
					a.schedule_start_date::date,
					b.email, 
					case 
					when substring(c.notes from 1 for 24) = 'CFP dari Reference Code ' then 'Reference Code yang dimasukan salah, sehingga Assign by System'
					when c.notes is null then 'Belum di assign'
					else 
						c.notes
					end ket
				from 
					cfp_schedules a INNER JOIN users b ON a.client_id = b.id
					LEFT JOIN cfp_clients c ON a.client_id = c.client_id
				where
					a.schedule_start_date::date BETWEEN '".$year."-".$month."-01' AND '".$year."-".$month."-".$last_date."'
				) z
				GROUP BY z.ket, z.schedule_start_date

			  $$,
			  $$ SELECT m FROM generate_series(1,31) m $$
			) AS (
			  	ket character varying(255), 
			  	\"t_1\" character varying(10),
				\"t_2\" character varying(10),
				\"t_3\" character varying(10),
				\"t_4\" character varying(10),
				\"t_5\" character varying(10),
				\"t_6\" character varying(10),
				\"t_7\" character varying(10),
				\"t_8\" character varying(10),
				\"t_9\" character varying(10),
				\"t_10\" character varying(10),
				\"t_11\" character varying(10),
				\"t_12\" character varying(10),
				\"t_13\" character varying(10),
				\"t_14\" character varying(10),
				\"t_15\" character varying(10),
				\"t_16\" character varying(10),
				\"t_17\" character varying(10),
				\"t_18\" character varying(10),
				\"t_19\" character varying(10),
				\"t_20\" character varying(10),
				\"t_21\" character varying(10),
				\"t_22\" character varying(10),
				\"t_23\" character varying(10),
				\"t_24\" character varying(10),
				\"t_25\" character varying(10),
				\"t_26\" character varying(10),
				\"t_27\" character varying(10),
				\"t_28\" character varying(10),
				\"t_29\" character varying(10),
				\"t_30\" character varying(10),
				\"t_31\" character varying(10)
			)
		");


		$dataConsultasionPerCFP = \DB::select("
			SELECT * FROM crosstab (
			  $$ 

				select 
					b.name,
					EXTRACT(DAY FROM a.schedule_start_date) as month, 
					count(*)
				from 
					cfp_schedules a INNER JOIN users b ON a.cfp_id = b.id
				where
					a.schedule_start_date::date BETWEEN '".$year."-".$month."-01' AND '".$year."-".$month."-".$last_date."'
				group by 
					b.name,
					EXTRACT(DAY FROM a.schedule_start_date)
				order by b.name

			  $$,
			  $$ SELECT m FROM generate_series(1,31) m $$
			) AS (
			  	name character varying(255), 
			  	\"t_1\" character varying(10),
				\"t_2\" character varying(10),
				\"t_3\" character varying(10),
				\"t_4\" character varying(10),
				\"t_5\" character varying(10),
				\"t_6\" character varying(10),
				\"t_7\" character varying(10),
				\"t_8\" character varying(10),
				\"t_9\" character varying(10),
				\"t_10\" character varying(10),
				\"t_11\" character varying(10),
				\"t_12\" character varying(10),
				\"t_13\" character varying(10),
				\"t_14\" character varying(10),
				\"t_15\" character varying(10),
				\"t_16\" character varying(10),
				\"t_17\" character varying(10),
				\"t_18\" character varying(10),
				\"t_19\" character varying(10),
				\"t_20\" character varying(10),
				\"t_21\" character varying(10),
				\"t_22\" character varying(10),
				\"t_23\" character varying(10),
				\"t_24\" character varying(10),
				\"t_25\" character varying(10),
				\"t_26\" character varying(10),
				\"t_27\" character varying(10),
				\"t_28\" character varying(10),
				\"t_29\" character varying(10),
				\"t_30\" character varying(10),
				\"t_31\" character varying(10)
			)
		");



		$dataFincheck = \DB::select("
			SELECT * FROM crosstab (
			  $$ 

			select
				case 
				when z.ket = 'CFP dari Reference Code ' then 'Reference Code yang dimasukan salah, sehingga Assign by System'
				when z.ket is null then 'Belum di assign'
				else 
					z.ket
				end ket, 
				EXTRACT(DAY FROM z.started_at) as month, 
				z.jumlah
			from
			(
			select 
				d.started_at::date
				, substring(c.notes from 1 for 24) ket
				, count(distinct a.email) jumlah
			from 
				users a inner join role_user b ON a.id = b.user_id
				LEFT JOIN cfp_clients c ON a.id = c.client_id
				INNER JOIN cycles d ON d.client_id = a.id
			where 
				b.role_id = '7'
				and d.cashflow_analysis_version_approved IS NOT null
				and d.started_at BETWEEN '".$year."-".$month."-01' AND '".$year."-".$month."-".$last_date."'
			group by substring(c.notes from 1 for 24), d.started_at::date
			order by substring(c.notes from 1 for 24), d.started_at::date
			) z

			$$,
			  $$ SELECT m FROM generate_series(1,31) m $$
			) AS (
			  	ket character varying(255), 
			  	\"t_1\" character varying(10),
				\"t_2\" character varying(10),
				\"t_3\" character varying(10),
				\"t_4\" character varying(10),
				\"t_5\" character varying(10),
				\"t_6\" character varying(10),
				\"t_7\" character varying(10),
				\"t_8\" character varying(10),
				\"t_9\" character varying(10),
				\"t_10\" character varying(10),
				\"t_11\" character varying(10),
				\"t_12\" character varying(10),
				\"t_13\" character varying(10),
				\"t_14\" character varying(10),
				\"t_15\" character varying(10),
				\"t_16\" character varying(10),
				\"t_17\" character varying(10),
				\"t_18\" character varying(10),
				\"t_19\" character varying(10),
				\"t_20\" character varying(10),
				\"t_21\" character varying(10),
				\"t_22\" character varying(10),
				\"t_23\" character varying(10),
				\"t_24\" character varying(10),
				\"t_25\" character varying(10),
				\"t_26\" character varying(10),
				\"t_27\" character varying(10),
				\"t_28\" character varying(10),
				\"t_29\" character varying(10),
				\"t_30\" character varying(10),
				\"t_31\" character varying(10)
			)
		");


		$dataFincheckByActiveVersion = \DB::select("
			SELECT * FROM crosstab (
			  $$ 

			select
				z.ket, 
				EXTRACT(DAY FROM z.approved_at) as month, 
				count(*) jumlah
			from
			(
			select
				a.approved_at::date,
				a.user_id, 
				case 
					when substring(b.notes from 1 for 24) = 'CFP dari Reference Code ' then 'Reference Code yang dimasukan salah, sehingga Assign by System'
					when b.notes is null then 'Belum di assign'
				else 
					b.notes
				end ket
			from 
				active_version_details a INNER JOIN cfp_clients b ON a.user_id = b.client_id
			where
				a.active_version_key = 'financialCheckup_cashflowAnalysis'
				and a.approved_at::date BETWEEN '".$year."-".$month."-01' AND '".$year."-".$month."-".$last_date."'
			) z
			GROUP BY z.ket, z.approved_at											

			$$,
			  $$ SELECT m FROM generate_series(1,31) m $$
			) AS (
			  	ket character varying(255), 
			  	\"t_1\" character varying(10),
				\"t_2\" character varying(10),
				\"t_3\" character varying(10),
				\"t_4\" character varying(10),
				\"t_5\" character varying(10),
				\"t_6\" character varying(10),
				\"t_7\" character varying(10),
				\"t_8\" character varying(10),
				\"t_9\" character varying(10),
				\"t_10\" character varying(10),
				\"t_11\" character varying(10),
				\"t_12\" character varying(10),
				\"t_13\" character varying(10),
				\"t_14\" character varying(10),
				\"t_15\" character varying(10),
				\"t_16\" character varying(10),
				\"t_17\" character varying(10),
				\"t_18\" character varying(10),
				\"t_19\" character varying(10),
				\"t_20\" character varying(10),
				\"t_21\" character varying(10),
				\"t_22\" character varying(10),
				\"t_23\" character varying(10),
				\"t_24\" character varying(10),
				\"t_25\" character varying(10),
				\"t_26\" character varying(10),
				\"t_27\" character varying(10),
				\"t_28\" character varying(10),
				\"t_29\" character varying(10),
				\"t_30\" character varying(10),
				\"t_31\" character varying(10)
			)
		");





		$dataWallet = \DB::select("
			SELECT * FROM crosstab (
			  $$ 

			select
				case 
				when z.ket = 'CFP dari Reference Code ' then 'Reference Code yang dimasukan salah, sehingga Assign by System'
				when z.ket is null then 'Belum di assign'
				else 
					z.ket
				end ket,
				EXTRACT(DAY FROM z.created_at) as month, 
				z.jumlah
			from
			(
			select 
				substring(c.notes from 1 for 24) ket
				, a.created_at::date
				, count(*) jumlah
			from 
				users a inner join role_user b ON a.id = b.user_id
				LEFT JOIN cfp_clients c ON a.id = c.client_id
			where 
				b.role_id = '7'
				and a.id in (
					select d.user_id from bank_statements d
				)
				and a.created_at BETWEEN '".$year."-".$month."-01' AND '".$year."-".$month."-".$last_date."'
			group by substring(c.notes from 1 for 24), a.created_at::date
			order by substring(c.notes from 1 for 24), a.created_at::date
			)z

			$$,
			  $$ SELECT m FROM generate_series(1,31) m $$
			) AS (
			  	ket character varying(255), 
			  	\"t_1\" character varying(10),
				\"t_2\" character varying(10),
				\"t_3\" character varying(10),
				\"t_4\" character varying(10),
				\"t_5\" character varying(10),
				\"t_6\" character varying(10),
				\"t_7\" character varying(10),
				\"t_8\" character varying(10),
				\"t_9\" character varying(10),
				\"t_10\" character varying(10),
				\"t_11\" character varying(10),
				\"t_12\" character varying(10),
				\"t_13\" character varying(10),
				\"t_14\" character varying(10),
				\"t_15\" character varying(10),
				\"t_16\" character varying(10),
				\"t_17\" character varying(10),
				\"t_18\" character varying(10),
				\"t_19\" character varying(10),
				\"t_20\" character varying(10),
				\"t_21\" character varying(10),
				\"t_22\" character varying(10),
				\"t_23\" character varying(10),
				\"t_24\" character varying(10),
				\"t_25\" character varying(10),
				\"t_26\" character varying(10),
				\"t_27\" character varying(10),
				\"t_28\" character varying(10),
				\"t_29\" character varying(10),
				\"t_30\" character varying(10),
				\"t_31\" character varying(10)
			)
		");



		$dataWalletByOpenWallet = \DB::select("
			SELECT * FROM crosstab (
			  $$ 

			select
				case 
				when z.ket = 'CFP dari Reference Code ' then 'Reference Code yang dimasukan salah, sehingga Assign by System'
				when z.ket is null then 'Belum di assign'
				else 
					z.ket
				end ket,
				EXTRACT(DAY FROM z.created_at) as month, 
				z.jumlah
			from
			(
			select 
				substring(c.notes from 1 for 24) ket
				, a.created_at::date
				, count(distinct a.user_id) jumlah
			from bank_accounts a inner join users b on a.user_id = b.id
				inner join cfp_clients c on b.id = c.client_id
			where
				a.created_at::date BETWEEN '".$year."-".$month."-01' AND '".$year."-".$month."-".$last_date."'
			group by substring(c.notes from 1 for 24), a.created_at::date
			order by substring(c.notes from 1 for 24), a.created_at::date
			)z

			$$,
			  $$ SELECT m FROM generate_series(1,31) m $$
			) AS (
			  	ket character varying(255), 
			  	\"t_1\" character varying(10),
				\"t_2\" character varying(10),
				\"t_3\" character varying(10),
				\"t_4\" character varying(10),
				\"t_5\" character varying(10),
				\"t_6\" character varying(10),
				\"t_7\" character varying(10),
				\"t_8\" character varying(10),
				\"t_9\" character varying(10),
				\"t_10\" character varying(10),
				\"t_11\" character varying(10),
				\"t_12\" character varying(10),
				\"t_13\" character varying(10),
				\"t_14\" character varying(10),
				\"t_15\" character varying(10),
				\"t_16\" character varying(10),
				\"t_17\" character varying(10),
				\"t_18\" character varying(10),
				\"t_19\" character varying(10),
				\"t_20\" character varying(10),
				\"t_21\" character varying(10),
				\"t_22\" character varying(10),
				\"t_23\" character varying(10),
				\"t_24\" character varying(10),
				\"t_25\" character varying(10),
				\"t_26\" character varying(10),
				\"t_27\" character varying(10),
				\"t_28\" character varying(10),
				\"t_29\" character varying(10),
				\"t_30\" character varying(10),
				\"t_31\" character varying(10)
			)
		");

		return view('backend.report.month', compact( 
			'dataRegister',
			'dataActive',
			'dataConsultasionPerRefcode',
			'dataConsultasionPerCFP',
			'dataFincheck',
			'dataFincheckByActiveVersion',
			'dataWallet',
			'dataWalletByOpenWallet'
		));
		
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		//
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}

}
