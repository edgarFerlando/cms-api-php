<!DOCTYPE html>
<html>
<head>
  <title>Report</title>

  <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.min.css"/ >
  <style>
    
    @import url("https://fonts.googleapis.com/css?family=Poppins:100,300");
    body {
      padding: 0;
      margin: 0;
      font-family: "Poppins", sans-serif;
      background-color: #eee;
    }

    .center {
        margin: auto;
        width: 50%;
        border: 5px solid #fff;
        padding: 10px;
        border-radius: 10px;
        margin-top: 30px;
        margin-bottom: 35px;

        /* box-shadow: 2px 2px #ccc; */
        box-shadow: 0px 13px 21px -10px rgba(0, 0, 0, 0.3);

        background-color: #82bee2;
    }

    .tr-line td {
      border-top: 1px solid #a3cee9;
      padding:5px;
    }

  </style>
</head>
<body>

  <div class="center">
     <input class="datetimepicker" id="start">
     <input class="datetimepicker" id="end">

     <button id="submit">Submit</button>
  </div>

  <div class="center">
    <table width="100%" border = '0'>
      <tbody>

        <!-- 1 Register -->
        <tr>
          <th colspan="3"><h3>Register</h3></th>
        </tr>
        <tr>
          <td><b>Total Register</b></td>
          <td>:</td>
          <td><b>{!! $countDataRegister !!}</b></td>
        </tr>
        <tr class="tr-line">
          <td colspan="3"></td>
        </tr>

        <tr>
          <td>Berdasarkan Assign</td><td></td><td></td>
        </tr>

        @foreach( $dataAssigns as $dataAssign )

        <tr>
          <td>&nbsp; - {!! $dataAssign->ket !!}</td>
          <td>:</td>
          <td>{!! $dataAssign->jumlah !!}</td>
        </tr>
        
        @endforeach        

      </tbody>
    </table>
  </div>


  <div class="center">
    <table width="100%" border = '0'>
      <tbody>

        <!-- 2 Email Verification -->
        <tr>
          <th colspan="3"><h3>Email Verification</h3></th>
        </tr>
        <tr>
          <td>Total User Active <i>(Click link activation)</i></td>
          <td>:</td>
          <td>{!! $countDataRegisterActive !!}</td>
        </tr>
        
      </tbody>
    </table>
  </div>

  <div class="center">
    <table width="100%" border = '0'>
      <tbody>

        <!-- 3 Consultasion -->
        <tr>
          <th colspan="3"><h3>Consultasion</h3></th>
        </tr>
        <tr>
          <td>Total User create consultation</td>
          <td></td>
          <td>: {!! $countDataConsulPeople !!} <i>Clinet</i></td>
        </tr>

        <tr class="tr-line">
          <td colspan="3"></td>
        </tr>
        <tr>
          <td>Berdasarkan Assign</td><td></td><td></td>
        </tr>

        @foreach( $dataConsulPeopleAssigns as $dataConsulPeopleAssign )

        <tr>
          <td>&nbsp; - {!! $dataConsulPeopleAssign->ket !!}</td>
          <td></td>
          <td>: {!! $dataConsulPeopleAssign->jumlah !!}</td>
        </tr>
        
        @endforeach
        
        <tr class="tr-line">
          <td colspan="3"></td>
        </tr>

        <tr>
          <th>CFP</th>
          <th>Jumlah client<br/>Yang membuat Schedule</th>
          <th>Total schedule</th>
        </tr>

        @foreach( $dataCFPScheduleConsults as $dataCFPScheduleConsult )

        <tr>
          <td>{!! $dataCFPScheduleConsult->name !!}</td>
          <td align="center">{!! $dataCFPScheduleConsult->jumlah_client_schedule !!}</td>
          <td align="center">{!! $dataCFPScheduleConsult->jumlah_schedule !!}</td>
        </tr>
        
        @endforeach

        <tr class="tr-line">
          <td colspan="3"></td>
        </tr>

        <tr>
          <td>Total Jadwal consultation</td>
          <td></td>
          <td>: {!! $countDataConsul !!} <i>Jadwal</i></td>
        </tr>
        <tr>
          <td>Total Jadwal consultation hari ini ke depan</td>
          <td></td>
          <td>: {!! $countDataConsulNext !!} <i>Jadwal</i></td>
        </tr>
        
      </tbody>
    </table>
  </div>

  <div class="center">
    <table width="100%" border = '0'>
      <tbody>

        <!-- 4 Financil Checkup -->
        <tr>
          <th colspan="3"><h3>Financil Check Up</h3></th>
        </tr>

        <tr>
          <td>Berdasarkan Assign</td><td></td><td></td>
        </tr>

        @foreach( $dataFincheckPeopleAssigns as $dataFincheckPeopleAssign )

        <tr>
          <td>&nbsp; - {!! $dataFincheckPeopleAssign->ket !!}</td>
          <td>:</td>
          <td>{!! $dataFincheckPeopleAssign->jumlah !!}</td>
        </tr>
        
        @endforeach
        
      </tbody>
    </table>
  </div>

  <div class="center">
    <table width="100%" border = '0'>
      <tbody>

        <!-- 5 Wallet -->
        <tr>
          <th colspan="3"><h3>Wallet</h3></th>
        </tr>
        <tr>
          <td>Total User active Wallet</td>
          <td>:</td>
          <td>{!! $countDataActiveWallet !!}</td>
        </tr>
        
      </tbody>
    </table>
  </div>

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.full.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.min.js"></script>
  
  <script>
    $(document).ready(function(){
        
        var url = $(location).attr('href');
        var segments = url.split( '/' );
        var p_start = segments[5];
        var p_end = segments[6];

        $("#start").val(p_start.replace("%20", " "));
        $("#end").val(p_end.replace("%20", " "));


        $("#submit").click(function(){
            var start = $("#start").val();
            var end = $("#end").val();

            window.location.href = location.protocol + "//" + location.host+"/admin/report/"+start+"/"+end;
        });

        jQuery('.datetimepicker').datetimepicker({
          format:'Y-m-d H:i:s'
        });

    });
  </script>

</body>
</html>