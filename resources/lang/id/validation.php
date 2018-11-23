<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    "accepted"             => "Inputan :attribute harus diterima.",
    "active_url"           => "Inputan :attribute bukan URL yang valid.",
    "after"                => "Inputan :attribute harus tanggal setelah :date.",
    "alpha"                => "Inputan :attribute harus berupa huruf.",
    "alpha_dash"           => "Inputan :attribute harus berupa huruf, angka, dan garis bawah.",
    "alpha_num"            => "Inputan :attribute harus berupa huruf dan angka.",
    "array"                => "Inputan :attribute harus berupa array.",
    "before"               => "Inputan :attribute harus tanggal sebelum :date.",
    "between"              => [
        "numeric" => "Inputan :attribute harus antara :min dan :max.",
        "file"    => "Inputan :attribute harus antara :min dan :max kilobytes.",
        "string"  => "Inputan :attribute harus antara :min dan :max karakter.",
        "array"   => "Inputan :attribute harus antara :min dan :max item.",
    ],
    "boolean"              => "Inputan :attribute harus berupa salah atau benar.",
    "confirmed"            => "Inputan konfirmasi :attribute tidak sama.",
    "date"                 => "Inputan :attribute bukan tanggal yang valid.",
    "date_format"          => "Inputan :attribute tidak sesuai dengan format :format.",
    "different"            => "Inputan :attribute dan :other harus berbeda.",
    "digits"               => "Inputan :attribute harus :digits angka.",
    "digits_between"       => "Inputan :attribute harus antara :min dan :max angka.",
    "email"                => "Inputan :attribute harus berupa alamat email.",
    "exists"               => "Inputan :attribute tidak ditemukan.",
    "image"                => "Inputan :attribute harus berupa gambar.",
    "in"                   => "Inputan :attribute tidak valid.",
    "integer"              => "Inputan :attribute harus berupa integer.",
    "ip"                   => "Inputan :attribute harus berupa alamat IP.",
    "max"                  => [
        "numeric" => "Inputan :attribute tidak boleh lebih dari :max.",
        "file"    => "Inputan :attribute tidak boleh lebih dari :max kilobytes.",
        "string"  => "Inputan :attribute tidak boleh lebih dari :max karakter.",
        "array"   => "Inputan :attribute tidak boleh memiliki lebih dari :max item.",
    ],
    "mimes"                => "Inputan :attribute harus berupa file: :values.",
    "min"                  => [
        "numeric" => "Inputan :attribute minimal harus :min.",
        "file"    => "Inputan :attribute minimal harus :min kilobytes.",
        "string"  => "Inputan :attribute minimal harus :min karakter.",
        "array"   => "Inputan :attribute minimal harus memiliki :min item.",
    ],
    "not_in"               => "Inputan :attribute yang dipilih tidak valid.",
    "numeric"              => "Inputan :attribute harus berupa nomor.",
    "regex"                => "Format isian :attribute tidak valid.",
    "required"             => "Inputan :attribute harus diisi.",
    "required_if"          => "Inputan :attribute harus diisi ketika :other diisi :value.",
    "required_with"        => "Inputan :attribute harus diisi ketika :values diisi.",
    "required_with_all"    => "Inputan :attribute harus diisi ketika :values diisi.",
    "required_without"     => "Inputan :attribute harus diisi ketika :values tidak diisi.",
    "required_without_all" => "Inputan :attribute harus diisi ketika tidak satupun :values diisi.",
    "same"                 => "Inputan :attribute dan :other harus sama.",
    "size"                 => [
        "numeric" => "Inputan :attribute harus :size.",
        "file"    => "Inputan :attribute harus :size kilobytes.",
        "string"  => "Inputan :attribute harus :size karakter.",
        "array"   => "Inputan :attribute harus :size item.",
    ],
    "unique"               => "Inputan :attribute sudah ada.",
    "url"                  => "Format isian :attribute tidak valid.",
    "timezone"             => "Inputan :attribute harus berupa timezone.",
    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom'               => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
        'schedule_start_date' => [
            'after' => "The :attribute must be a date after today or today."
        ],
        'schedule_available_time_slot' => [
            'after' => "The :attribute must be use date after today or today."
        ],
		'date_of_birth' => [
			'before' => "You must be at least :attribute years old to register. "
		]
    ],
    'timeslot_available' => "Inputan :attribute tidak tersedia.",
    'taxo_exist' => "Inputan :attribute tidak ditemukan.",
    'working_hours' => "Waktu Konsultasi di luar jam kerja.",
    'is_schedule_expired' => "Tanggal yang dipilih tidak berlaku.",
    'cannot_booking_same_day' => "Jadwal Konsultasi tidak bisa dijadwalkan pada Hari Ini.",
    'end_of_month' => "Periode tidak valid.",
    'cfp_available' => "CFP is not available on this :attribute.",
    'unique_actual_interest_rate' => "this :attribute is already used for this product.",
    'cannot_booking_saturday' => "Jadwal Konsultasi tidak bisa dijadwalkan pada Hari Sabtu.",
    'cannot_booking_sunday' => "Jadwal Konsultasi tidak bisa dijadwalkan pada Hari Minggu.",
    'cannot_booking_national_day_indonesia' => "Jadwal Konsultasi tidak bisa dijadwalkan pada Hari Libur Nasional.",
    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes'           => [],

];
