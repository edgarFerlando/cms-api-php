<!DOCTYPE html>
<html>
<head>
  <title>Report</title>
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
        margin-top: 50px;

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
    <table width="100%" border = '0'>
      <tbody>
        <tr>
          <td><b>Total Register</b></td>
          <td>:</td>
          <td><b>{!! $countDataRegister !!}</b></td>
        </tr>
        <tr>
          <td>Total Register Today</td>
          <td>:</td>
          <td>{!! $countDataRegisterToday !!}</td>
        </tr>
        <tr>
          <td>Total Register Yesterday</td>
          <td>:</td>
          <td>{!! $countDataRegisterYesterday !!}</td>
        </tr>
        <tr class="tr-line">
          <td colspan="3"></td>
        </tr>
        <tr>
          <td>Total User with Verified E-Mail</td>
          <td>:</td>
          <td>{!! $countDataRegisterActive !!}</td>
        </tr>
        <tr>
          <td>Total User active Wallet</td>
          <td>:</td>
          <td>{!! $countDataActiveWallet !!}</td>
        </tr>
        <tr class="tr-line">
          <td colspan="3"></td>
        </tr>
        <tr>
          <td>Total User create consultation</td>
          <td>:</td>
          <td>{!! $countDataConsulPeople !!} <i>Client</i></td>
        </tr>
        <tr>
          <td>Total Jadwal consultation</td>
          <td>:</td>
          <td>{!! $countDataConsul !!} <i>Jadwal</i></td>
        </tr>
        <tr>
          <td>Total Jadwal consultation hari ini ke depan</td>
          <td>:</td>
          <td>{!! $countDataConsulNext !!} <i>Jadwal</i></td>
        </tr>
        <tr class="tr-line">
          <td colspan="3"></td>
        </tr>

        <tr>
          <th colspan="3"><h3>Reference Code</h3></th>
        </tr>


        @foreach( $dataAssigns as $dataAssign )

        <tr>
          <td>{!! $dataAssign->ket !!}</td>
          <td>:</td>
          <td>{!! $dataAssign->jumlah !!}</td>
        </tr>
        
        @endforeach


        
      </tbody>
    </table>
  </div>

</body>
</html>