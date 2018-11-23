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

    table tr, table td {
      padding: 5px;
      padding-left:7px;
      padding-right: 7px;
      margin: 1px;
    }

    table {
      border-collapse: collapse;
    }

    table, td, th {
      border: 1px solid #addaf5;
    }

  </style>
</head>
<body>

  <div class="center">
     <input class="" id="year">
     <input class="" id="month">

     <button id="submit">Submit</button>
  </div>

  <div class="center">
    <h3 align="center">Register</h3>
    <table width="100%" border = '0'>
      <thead>
        <th>ket</th>
        <th>1</th>
        <th>2</th>
        <th>3</th>
        <th>4</th>
        <th>5</th>
        <th>6</th>
        <th>7</th>
        <th>8</th>
        <th>9</th>
        <th>10</th>
        <th>11</th>
        <th>12</th>
        <th>13</th>
        <th>14</th>
        <th>15</th>
        <th>16</th>
        <th>17</th>
        <th>18</th>
        <th>19</th>
        <th>20</th>
        <th>21</th>
        <th>22</th>
        <th>23</th>
        <th>24</th>
        <th>25</th>
        <th>26</th>
        <th>27</th>
        <th>28</th>
        <th>29</th>
        <th>30</th>
        <th>31</th>
      </thead>
      <tbody>

        @foreach( $dataRegister as $itemRegister )

        <tr>
          <th>{!! $itemRegister->ket !!}</th>
          <th>{!! $itemRegister->t_1 !!}</th>
          <th>{!! $itemRegister->t_2 !!}</th>
          <th>{!! $itemRegister->t_3 !!}</th>
          <th>{!! $itemRegister->t_4 !!}</th>
          <th>{!! $itemRegister->t_5 !!}</th>
          <th>{!! $itemRegister->t_6 !!}</th>
          <th>{!! $itemRegister->t_7 !!}</th>
          <th>{!! $itemRegister->t_8 !!}</th>
          <th>{!! $itemRegister->t_9 !!}</th>
          <th>{!! $itemRegister->t_10 !!}</th>
          <th>{!! $itemRegister->t_11 !!}</th>
          <th>{!! $itemRegister->t_12 !!}</th>
          <th>{!! $itemRegister->t_13 !!}</th>
          <th>{!! $itemRegister->t_14 !!}</th>
          <th>{!! $itemRegister->t_15 !!}</th>
          <th>{!! $itemRegister->t_16 !!}</th>
          <th>{!! $itemRegister->t_17 !!}</th>
          <th>{!! $itemRegister->t_18 !!}</th>
          <th>{!! $itemRegister->t_19 !!}</th>
          <th>{!! $itemRegister->t_20 !!}</th>
          <th>{!! $itemRegister->t_21 !!}</th>
          <th>{!! $itemRegister->t_22 !!}</th>
          <th>{!! $itemRegister->t_23 !!}</th>
          <th>{!! $itemRegister->t_24 !!}</th>
          <th>{!! $itemRegister->t_25 !!}</th>
          <th>{!! $itemRegister->t_26 !!}</th>
          <th>{!! $itemRegister->t_27 !!}</th>
          <th>{!! $itemRegister->t_28 !!}</th>
          <th>{!! $itemRegister->t_29 !!}</th>
          <th>{!! $itemRegister->t_30 !!}</th>
          <th>{!! $itemRegister->t_31 !!}</th>
        </tr>
        
        @endforeach

      </tbody>
    </table>
  </div>




  <div class="center">
    <h3 align="center">Active</h3>
    <table width="100%" border = '0'>
      <thead>
        <th>ket</th>
        <th>1</th>
        <th>2</th>
        <th>3</th>
        <th>4</th>
        <th>5</th>
        <th>6</th>
        <th>7</th>
        <th>8</th>
        <th>9</th>
        <th>10</th>
        <th>11</th>
        <th>12</th>
        <th>13</th>
        <th>14</th>
        <th>15</th>
        <th>16</th>
        <th>17</th>
        <th>18</th>
        <th>19</th>
        <th>20</th>
        <th>21</th>
        <th>22</th>
        <th>23</th>
        <th>24</th>
        <th>25</th>
        <th>26</th>
        <th>27</th>
        <th>28</th>
        <th>29</th>
        <th>30</th>
        <th>31</th>
      </thead>
      <tbody>

        @foreach( $dataActive as $itemRegister )

        <tr>
          <th>{!! $itemRegister->ket !!}</th>
          <th>{!! $itemRegister->t_1 !!}</th>
          <th>{!! $itemRegister->t_2 !!}</th>
          <th>{!! $itemRegister->t_3 !!}</th>
          <th>{!! $itemRegister->t_4 !!}</th>
          <th>{!! $itemRegister->t_5 !!}</th>
          <th>{!! $itemRegister->t_6 !!}</th>
          <th>{!! $itemRegister->t_7 !!}</th>
          <th>{!! $itemRegister->t_8 !!}</th>
          <th>{!! $itemRegister->t_9 !!}</th>
          <th>{!! $itemRegister->t_10 !!}</th>
          <th>{!! $itemRegister->t_11 !!}</th>
          <th>{!! $itemRegister->t_12 !!}</th>
          <th>{!! $itemRegister->t_13 !!}</th>
          <th>{!! $itemRegister->t_14 !!}</th>
          <th>{!! $itemRegister->t_15 !!}</th>
          <th>{!! $itemRegister->t_16 !!}</th>
          <th>{!! $itemRegister->t_17 !!}</th>
          <th>{!! $itemRegister->t_18 !!}</th>
          <th>{!! $itemRegister->t_19 !!}</th>
          <th>{!! $itemRegister->t_20 !!}</th>
          <th>{!! $itemRegister->t_21 !!}</th>
          <th>{!! $itemRegister->t_22 !!}</th>
          <th>{!! $itemRegister->t_23 !!}</th>
          <th>{!! $itemRegister->t_24 !!}</th>
          <th>{!! $itemRegister->t_25 !!}</th>
          <th>{!! $itemRegister->t_26 !!}</th>
          <th>{!! $itemRegister->t_27 !!}</th>
          <th>{!! $itemRegister->t_28 !!}</th>
          <th>{!! $itemRegister->t_29 !!}</th>
          <th>{!! $itemRegister->t_30 !!}</th>
          <th>{!! $itemRegister->t_31 !!}</th>
        </tr>
        
        @endforeach

      </tbody>
    </table>
  </div>





  <div class="center">
    <h3 align="center">Consultasion Per RefCode</h3>
    <table width="100%" border = '0'>
      <thead>
        <th>ket</th>
        <th>1</th>
        <th>2</th>
        <th>3</th>
        <th>4</th>
        <th>5</th>
        <th>6</th>
        <th>7</th>
        <th>8</th>
        <th>9</th>
        <th>10</th>
        <th>11</th>
        <th>12</th>
        <th>13</th>
        <th>14</th>
        <th>15</th>
        <th>16</th>
        <th>17</th>
        <th>18</th>
        <th>19</th>
        <th>20</th>
        <th>21</th>
        <th>22</th>
        <th>23</th>
        <th>24</th>
        <th>25</th>
        <th>26</th>
        <th>27</th>
        <th>28</th>
        <th>29</th>
        <th>30</th>
        <th>31</th>
      </thead>
      <tbody>

        @foreach( $dataConsultasionPerRefcode as $itemRegister )

        <tr>
          <th>{!! $itemRegister->ket !!}</th>
          <th>{!! $itemRegister->t_1 !!}</th>
          <th>{!! $itemRegister->t_2 !!}</th>
          <th>{!! $itemRegister->t_3 !!}</th>
          <th>{!! $itemRegister->t_4 !!}</th>
          <th>{!! $itemRegister->t_5 !!}</th>
          <th>{!! $itemRegister->t_6 !!}</th>
          <th>{!! $itemRegister->t_7 !!}</th>
          <th>{!! $itemRegister->t_8 !!}</th>
          <th>{!! $itemRegister->t_9 !!}</th>
          <th>{!! $itemRegister->t_10 !!}</th>
          <th>{!! $itemRegister->t_11 !!}</th>
          <th>{!! $itemRegister->t_12 !!}</th>
          <th>{!! $itemRegister->t_13 !!}</th>
          <th>{!! $itemRegister->t_14 !!}</th>
          <th>{!! $itemRegister->t_15 !!}</th>
          <th>{!! $itemRegister->t_16 !!}</th>
          <th>{!! $itemRegister->t_17 !!}</th>
          <th>{!! $itemRegister->t_18 !!}</th>
          <th>{!! $itemRegister->t_19 !!}</th>
          <th>{!! $itemRegister->t_20 !!}</th>
          <th>{!! $itemRegister->t_21 !!}</th>
          <th>{!! $itemRegister->t_22 !!}</th>
          <th>{!! $itemRegister->t_23 !!}</th>
          <th>{!! $itemRegister->t_24 !!}</th>
          <th>{!! $itemRegister->t_25 !!}</th>
          <th>{!! $itemRegister->t_26 !!}</th>
          <th>{!! $itemRegister->t_27 !!}</th>
          <th>{!! $itemRegister->t_28 !!}</th>
          <th>{!! $itemRegister->t_29 !!}</th>
          <th>{!! $itemRegister->t_30 !!}</th>
          <th>{!! $itemRegister->t_31 !!}</th>
        </tr>
        
        @endforeach

      </tbody>
    </table>
  </div>








  <div class="center">
    <h3 align="center">Consultasion Per CFP</h3>
    <table width="100%" border = '0'>
      <thead>
        <th>ket</th>
        <th>1</th>
        <th>2</th>
        <th>3</th>
        <th>4</th>
        <th>5</th>
        <th>6</th>
        <th>7</th>
        <th>8</th>
        <th>9</th>
        <th>10</th>
        <th>11</th>
        <th>12</th>
        <th>13</th>
        <th>14</th>
        <th>15</th>
        <th>16</th>
        <th>17</th>
        <th>18</th>
        <th>19</th>
        <th>20</th>
        <th>21</th>
        <th>22</th>
        <th>23</th>
        <th>24</th>
        <th>25</th>
        <th>26</th>
        <th>27</th>
        <th>28</th>
        <th>29</th>
        <th>30</th>
        <th>31</th>
      </thead>
      <tbody>

        @foreach( $dataConsultasionPerCFP as $itemRegister )

        <tr>
          <th>{!! $itemRegister->name !!}</th>
          <th>{!! $itemRegister->t_1 !!}</th>
          <th>{!! $itemRegister->t_2 !!}</th>
          <th>{!! $itemRegister->t_3 !!}</th>
          <th>{!! $itemRegister->t_4 !!}</th>
          <th>{!! $itemRegister->t_5 !!}</th>
          <th>{!! $itemRegister->t_6 !!}</th>
          <th>{!! $itemRegister->t_7 !!}</th>
          <th>{!! $itemRegister->t_8 !!}</th>
          <th>{!! $itemRegister->t_9 !!}</th>
          <th>{!! $itemRegister->t_10 !!}</th>
          <th>{!! $itemRegister->t_11 !!}</th>
          <th>{!! $itemRegister->t_12 !!}</th>
          <th>{!! $itemRegister->t_13 !!}</th>
          <th>{!! $itemRegister->t_14 !!}</th>
          <th>{!! $itemRegister->t_15 !!}</th>
          <th>{!! $itemRegister->t_16 !!}</th>
          <th>{!! $itemRegister->t_17 !!}</th>
          <th>{!! $itemRegister->t_18 !!}</th>
          <th>{!! $itemRegister->t_19 !!}</th>
          <th>{!! $itemRegister->t_20 !!}</th>
          <th>{!! $itemRegister->t_21 !!}</th>
          <th>{!! $itemRegister->t_22 !!}</th>
          <th>{!! $itemRegister->t_23 !!}</th>
          <th>{!! $itemRegister->t_24 !!}</th>
          <th>{!! $itemRegister->t_25 !!}</th>
          <th>{!! $itemRegister->t_26 !!}</th>
          <th>{!! $itemRegister->t_27 !!}</th>
          <th>{!! $itemRegister->t_28 !!}</th>
          <th>{!! $itemRegister->t_29 !!}</th>
          <th>{!! $itemRegister->t_30 !!}</th>
          <th>{!! $itemRegister->t_31 !!}</th>
        </tr>
        
        @endforeach

      </tbody>
    </table>
  </div>








  <div class="center">
    <h3 align="center">Financial Checkup</h3>
    <table width="100%" border = '0'>
      <thead>
        <th>ket</th>
        <th>1</th>
        <th>2</th>
        <th>3</th>
        <th>4</th>
        <th>5</th>
        <th>6</th>
        <th>7</th>
        <th>8</th>
        <th>9</th>
        <th>10</th>
        <th>11</th>
        <th>12</th>
        <th>13</th>
        <th>14</th>
        <th>15</th>
        <th>16</th>
        <th>17</th>
        <th>18</th>
        <th>19</th>
        <th>20</th>
        <th>21</th>
        <th>22</th>
        <th>23</th>
        <th>24</th>
        <th>25</th>
        <th>26</th>
        <th>27</th>
        <th>28</th>
        <th>29</th>
        <th>30</th>
        <th>31</th>
      </thead>
      <tbody>

        @foreach( $dataFincheck as $itemRegister )

        <tr>
          <th>{!! $itemRegister->ket !!}</th>
          <th>{!! $itemRegister->t_1 !!}</th>
          <th>{!! $itemRegister->t_2 !!}</th>
          <th>{!! $itemRegister->t_3 !!}</th>
          <th>{!! $itemRegister->t_4 !!}</th>
          <th>{!! $itemRegister->t_5 !!}</th>
          <th>{!! $itemRegister->t_6 !!}</th>
          <th>{!! $itemRegister->t_7 !!}</th>
          <th>{!! $itemRegister->t_8 !!}</th>
          <th>{!! $itemRegister->t_9 !!}</th>
          <th>{!! $itemRegister->t_10 !!}</th>
          <th>{!! $itemRegister->t_11 !!}</th>
          <th>{!! $itemRegister->t_12 !!}</th>
          <th>{!! $itemRegister->t_13 !!}</th>
          <th>{!! $itemRegister->t_14 !!}</th>
          <th>{!! $itemRegister->t_15 !!}</th>
          <th>{!! $itemRegister->t_16 !!}</th>
          <th>{!! $itemRegister->t_17 !!}</th>
          <th>{!! $itemRegister->t_18 !!}</th>
          <th>{!! $itemRegister->t_19 !!}</th>
          <th>{!! $itemRegister->t_20 !!}</th>
          <th>{!! $itemRegister->t_21 !!}</th>
          <th>{!! $itemRegister->t_22 !!}</th>
          <th>{!! $itemRegister->t_23 !!}</th>
          <th>{!! $itemRegister->t_24 !!}</th>
          <th>{!! $itemRegister->t_25 !!}</th>
          <th>{!! $itemRegister->t_26 !!}</th>
          <th>{!! $itemRegister->t_27 !!}</th>
          <th>{!! $itemRegister->t_28 !!}</th>
          <th>{!! $itemRegister->t_29 !!}</th>
          <th>{!! $itemRegister->t_30 !!}</th>
          <th>{!! $itemRegister->t_31 !!}</th>
        </tr>
        
        @endforeach

      </tbody>
    </table>

    <br/>
    <div>(Get By tabel Active version)</div>
    <table width="100%" border = '0'>
      <thead>
        <th>ket</th>
        <th>1</th>
        <th>2</th>
        <th>3</th>
        <th>4</th>
        <th>5</th>
        <th>6</th>
        <th>7</th>
        <th>8</th>
        <th>9</th>
        <th>10</th>
        <th>11</th>
        <th>12</th>
        <th>13</th>
        <th>14</th>
        <th>15</th>
        <th>16</th>
        <th>17</th>
        <th>18</th>
        <th>19</th>
        <th>20</th>
        <th>21</th>
        <th>22</th>
        <th>23</th>
        <th>24</th>
        <th>25</th>
        <th>26</th>
        <th>27</th>
        <th>28</th>
        <th>29</th>
        <th>30</th>
        <th>31</th>
      </thead>
      <tbody>

        @foreach( $dataFincheckByActiveVersion as $itemRegister )

        <tr>
          <th>{!! $itemRegister->ket !!}</th>
          <th>{!! $itemRegister->t_1 !!}</th>
          <th>{!! $itemRegister->t_2 !!}</th>
          <th>{!! $itemRegister->t_3 !!}</th>
          <th>{!! $itemRegister->t_4 !!}</th>
          <th>{!! $itemRegister->t_5 !!}</th>
          <th>{!! $itemRegister->t_6 !!}</th>
          <th>{!! $itemRegister->t_7 !!}</th>
          <th>{!! $itemRegister->t_8 !!}</th>
          <th>{!! $itemRegister->t_9 !!}</th>
          <th>{!! $itemRegister->t_10 !!}</th>
          <th>{!! $itemRegister->t_11 !!}</th>
          <th>{!! $itemRegister->t_12 !!}</th>
          <th>{!! $itemRegister->t_13 !!}</th>
          <th>{!! $itemRegister->t_14 !!}</th>
          <th>{!! $itemRegister->t_15 !!}</th>
          <th>{!! $itemRegister->t_16 !!}</th>
          <th>{!! $itemRegister->t_17 !!}</th>
          <th>{!! $itemRegister->t_18 !!}</th>
          <th>{!! $itemRegister->t_19 !!}</th>
          <th>{!! $itemRegister->t_20 !!}</th>
          <th>{!! $itemRegister->t_21 !!}</th>
          <th>{!! $itemRegister->t_22 !!}</th>
          <th>{!! $itemRegister->t_23 !!}</th>
          <th>{!! $itemRegister->t_24 !!}</th>
          <th>{!! $itemRegister->t_25 !!}</th>
          <th>{!! $itemRegister->t_26 !!}</th>
          <th>{!! $itemRegister->t_27 !!}</th>
          <th>{!! $itemRegister->t_28 !!}</th>
          <th>{!! $itemRegister->t_29 !!}</th>
          <th>{!! $itemRegister->t_30 !!}</th>
          <th>{!! $itemRegister->t_31 !!}</th>
        </tr>
        
        @endforeach

      </tbody>
    </table>

  </div>





  <div class="center">
    <h3 align="center">Active Wallet</h3>
    <div>(Data akan bergerak, karena data disini diambil dari tanggal user_create. bukan tanggal user open wallet)</div>
    <table width="100%" border = '0'>
      <thead>
        <th>ket</th>
        <th>1</th>
        <th>2</th>
        <th>3</th>
        <th>4</th>
        <th>5</th>
        <th>6</th>
        <th>7</th>
        <th>8</th>
        <th>9</th>
        <th>10</th>
        <th>11</th>
        <th>12</th>
        <th>13</th>
        <th>14</th>
        <th>15</th>
        <th>16</th>
        <th>17</th>
        <th>18</th>
        <th>19</th>
        <th>20</th>
        <th>21</th>
        <th>22</th>
        <th>23</th>
        <th>24</th>
        <th>25</th>
        <th>26</th>
        <th>27</th>
        <th>28</th>
        <th>29</th>
        <th>30</th>
        <th>31</th>
      </thead>
      <tbody>

        @foreach( $dataWallet as $itemRegister )

        <tr>
          <th>{!! $itemRegister->ket !!}</th>
          <th>{!! $itemRegister->t_1 !!}</th>
          <th>{!! $itemRegister->t_2 !!}</th>
          <th>{!! $itemRegister->t_3 !!}</th>
          <th>{!! $itemRegister->t_4 !!}</th>
          <th>{!! $itemRegister->t_5 !!}</th>
          <th>{!! $itemRegister->t_6 !!}</th>
          <th>{!! $itemRegister->t_7 !!}</th>
          <th>{!! $itemRegister->t_8 !!}</th>
          <th>{!! $itemRegister->t_9 !!}</th>
          <th>{!! $itemRegister->t_10 !!}</th>
          <th>{!! $itemRegister->t_11 !!}</th>
          <th>{!! $itemRegister->t_12 !!}</th>
          <th>{!! $itemRegister->t_13 !!}</th>
          <th>{!! $itemRegister->t_14 !!}</th>
          <th>{!! $itemRegister->t_15 !!}</th>
          <th>{!! $itemRegister->t_16 !!}</th>
          <th>{!! $itemRegister->t_17 !!}</th>
          <th>{!! $itemRegister->t_18 !!}</th>
          <th>{!! $itemRegister->t_19 !!}</th>
          <th>{!! $itemRegister->t_20 !!}</th>
          <th>{!! $itemRegister->t_21 !!}</th>
          <th>{!! $itemRegister->t_22 !!}</th>
          <th>{!! $itemRegister->t_23 !!}</th>
          <th>{!! $itemRegister->t_24 !!}</th>
          <th>{!! $itemRegister->t_25 !!}</th>
          <th>{!! $itemRegister->t_26 !!}</th>
          <th>{!! $itemRegister->t_27 !!}</th>
          <th>{!! $itemRegister->t_28 !!}</th>
          <th>{!! $itemRegister->t_29 !!}</th>
          <th>{!! $itemRegister->t_30 !!}</th>
          <th>{!! $itemRegister->t_31 !!}</th>
        </tr>
        
        @endforeach

      </tbody>
    </table>

    <br/>
    <div>(Data diambil dari tanggal user open wallet. bukan tanggal user created)</div>
    <table width="100%" border = '0'>
      <thead>
        <th>ket</th>
        <th>1</th>
        <th>2</th>
        <th>3</th>
        <th>4</th>
        <th>5</th>
        <th>6</th>
        <th>7</th>
        <th>8</th>
        <th>9</th>
        <th>10</th>
        <th>11</th>
        <th>12</th>
        <th>13</th>
        <th>14</th>
        <th>15</th>
        <th>16</th>
        <th>17</th>
        <th>18</th>
        <th>19</th>
        <th>20</th>
        <th>21</th>
        <th>22</th>
        <th>23</th>
        <th>24</th>
        <th>25</th>
        <th>26</th>
        <th>27</th>
        <th>28</th>
        <th>29</th>
        <th>30</th>
        <th>31</th>
      </thead>
      <tbody>

        @foreach( $dataWalletByOpenWallet as $itemRegister )

        <tr>
          <th>{!! $itemRegister->ket !!}</th>
          <th>{!! $itemRegister->t_1 !!}</th>
          <th>{!! $itemRegister->t_2 !!}</th>
          <th>{!! $itemRegister->t_3 !!}</th>
          <th>{!! $itemRegister->t_4 !!}</th>
          <th>{!! $itemRegister->t_5 !!}</th>
          <th>{!! $itemRegister->t_6 !!}</th>
          <th>{!! $itemRegister->t_7 !!}</th>
          <th>{!! $itemRegister->t_8 !!}</th>
          <th>{!! $itemRegister->t_9 !!}</th>
          <th>{!! $itemRegister->t_10 !!}</th>
          <th>{!! $itemRegister->t_11 !!}</th>
          <th>{!! $itemRegister->t_12 !!}</th>
          <th>{!! $itemRegister->t_13 !!}</th>
          <th>{!! $itemRegister->t_14 !!}</th>
          <th>{!! $itemRegister->t_15 !!}</th>
          <th>{!! $itemRegister->t_16 !!}</th>
          <th>{!! $itemRegister->t_17 !!}</th>
          <th>{!! $itemRegister->t_18 !!}</th>
          <th>{!! $itemRegister->t_19 !!}</th>
          <th>{!! $itemRegister->t_20 !!}</th>
          <th>{!! $itemRegister->t_21 !!}</th>
          <th>{!! $itemRegister->t_22 !!}</th>
          <th>{!! $itemRegister->t_23 !!}</th>
          <th>{!! $itemRegister->t_24 !!}</th>
          <th>{!! $itemRegister->t_25 !!}</th>
          <th>{!! $itemRegister->t_26 !!}</th>
          <th>{!! $itemRegister->t_27 !!}</th>
          <th>{!! $itemRegister->t_28 !!}</th>
          <th>{!! $itemRegister->t_29 !!}</th>
          <th>{!! $itemRegister->t_30 !!}</th>
          <th>{!! $itemRegister->t_31 !!}</th>
        </tr>
        
        @endforeach

      </tbody>
    </table>
  </div>




  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  
  <script>
    $(document).ready(function(){
        
        var url = $(location).attr('href');
        var segments = url.split( '/' );
        var p_year = segments[6];
        var p_month = segments[7];

        $("#year").val(p_year);
        $("#month").val(p_month);


        $("#submit").click(function(){
            var year = $("#year").val();
            var month = $("#month").val();

            window.location.href = location.protocol + "//" + location.host+"/admin/report/month/"+year+"/"+month;
        });

    });
  </script>

</body>
</html>