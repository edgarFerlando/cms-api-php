<!DOCTYPE html>
<html>
<head>
	<title></title>
	<style>
		@import url("https://fonts.googleapis.com/css?family=Montserrat");
		@import url("https://fonts.googleapis.com/css?family=Poppins:100,300");
		body {
			padding: 0;
			margin: 0;
			font-family: "Poppins", sans-serif;
		}
		.container {
			width: 850px;
			margin: auto;
    		padding: 10px;
		}
		header {
			background-color: #2477c5;
			padding: 5px;
			padding-top: 15px;
		}
		.logo_fun img {
			width: 200px;
		}
		.title {
			color: #2477c5;
		}
			.title h1 {
				font-size: 35px;
			}
		.content {
			background-color: #eee;
			font-size: 25px;
			color: #444;
		}
			.content a {
				color: #2477c5;
				text-decoration:none;
			}
		.bottom-content {
			color: #2477c5;
			margin: 100px;
		}
		footer {
			background-color: #2477c5;
			padding: 20px;
			color: #fff;
			font-size: 11px;
			line-height: 0.4cm;
		}
			.column {
			    float: left;
			    padding: 10px;
			}
				.col-1 {
					width: 25%;
				}
					.col-1 ul {
					    display: block;
					    list-style-type: none;
					    -webkit-margin-before: 1em;
					    -webkit-margin-after: 1em;
					    -webkit-margin-start: 0px;
					    -webkit-margin-end: 0px;
					    -webkit-padding-start: 0px;
					    margin: 0; /* To remove default bottom margin */ 
    					padding: 0;
					}
					.col-1 ul li {
					 	margin-bottom: 10px;   
					}
					.col-1 .kontak a {
						text-decoration:none;
						color: #fff;
					}
				.col-2 {
					width: 45%;
					text-align: justify;
					padding-right: 10px;
					margin-right: 20px;
				}
					.img-center {
					    display: block;
					    margin-left: auto;
					    margin-right: auto;
					    width: 50%;
					}
					img.img-center {
						width: 170px;
						padding-bottom: 10px;
					}
					.col-2 .box-sosmed {
						color: #fff;
						text-align: center;
						padding-top: 45px;
					}
						.col-2 .box-sosmed img {
							height: 16px;
							padding-right: 15px;
						}
				.col-3 {
					width: 20%;
				}
					.box-img {
					   display: flex;
					   align-items:center;
					   padding-bottom: 10px;
					}
						.box-img a {
						   	color: #fff;
							text-decoration:none;
						}
					.box-img img {
						width: 17px;
						max-height: 15px;
						padding-right: 5px;
					}
			.row:after {
			    content: "";
			    display: table;
			    clear: both;
			}
			.footer {
				background-color: #2477c5;
				padding: 20px;
				color: #fff;
				font-size: 11px;
				line-height: 0.4cm;
			}
				.footer .copy-right {
					text-align: center;
					padding-top: 15px;
					font-size: 10px;
					min-width: 300px;
				}
			@media only screen and (max-width: 600px) {
				.col-1, .col-2, .col-3 {
					width: 100%;
				}
				.container {
					width: 100%;
					margin: 0px;
    				padding: 3px;
				}
			}
	</style>
</head>
<body>
	<header>
		<div class="container">
			<a href="https://fundtastic.co.id/" class="logo_fun"><img src="https://raw.githubusercontent.com/gugundwipermana/_icon_share/master/foundtastic.png" /></a>
		</div>
	</header>
	<div class="title">
		<div class="container">
			<h1>Reset Password</h1>
		</div>
	</div>
	<div class="content">
		<div class="container">
			<p>
				Halo, Email ini telah dikirimkan kepada Anda sehingga Anda dapat mereset kata sandi Anda. <br />
				Silakan klik tautan di bawah ini untuk menyelesaikan proses pengaturan ulang kata sandi. Tautan ini akan kedaluwarsa dalam 1 jam<br /><br />
			</p>
			<p>
				<a href="{!! URL::route('reset-password', [ 'token' => $token ]) !!}">{!! trans('app.reset_password') !!}</a><br /><br />
			</p>
			<p>Perencana Keuangan Fundtastic telah menerima jadwal yang kamu pilih. Selanjutnya, yuk, kita sama-sama cek kesehatan finansialmu <a href="http://fundtastic.co.id/">di sini.</a></p>
			<p>Jika kamu mau bertanya-tanya sesuatu bisa lihat halaman <a href="http://fundtastic.co.id/">Syarat & Ketentuan, F.A.Q,</a> atau menghubungi kami <a href="http://fundtastic.co.id/">di sini.</a></p>
		</div>
	</div>
	<div class="bottom-content">
	</div>
	<footer>
		<div class="container">
			<div class="row">
				<div class="column col-1">
					<h3>Kontak</h3>
					<ul class="kontak">
						<li><a href="http://fundtastic.co.id/">Tentang Fundtastic</a></li>
						<li><a href="http://fundtastic.co.id/syarat-ketentuan.html">Syarat dan Kebijakan</a></li>
						<li><a href="http://fundtastic.co.id/">Kebijakan privasi</a></li>
						<li><a href="http://fundtastic.co.id/">Kontak</a></li>
						<li><a href="http://fundtastic.co.id/">FAQ</a></li>
					</ul>
				</div>
				<div class="column col-2">
					<img src="https://raw.githubusercontent.com/gugundwipermana/_icon_share/master/foundtastic.png" class="img-center" />
					<p>Foundtastic adalah sebuah Revolusi Keuangan. Kami membantu masyarakat Indonesia memperoleh kehidupan yang lebih baik melalui jasa perencanaan keuangan. Demi menyentuh seluruh kalangan masyarakat, Foundtastic menyediakan aplikasi yang dapat diakses dengan mudah.</p>
					<div class="box-sosmed">
					    <a href="https://www.facebook.com/234511757360344/posts/248944345917085/"><img src="https://raw.githubusercontent.com/gugundwipermana/_icon_share/master/facebook.png"></a>
					    <a href="https://twitter.com/fundtasticid/status/1019027677733253120?s=21"><img src="https://raw.githubusercontent.com/gugundwipermana/_icon_share/master/twitter.png"></a>
					    <a href=""><img src="https://raw.githubusercontent.com/gugundwipermana/_icon_share/master/pinterest.png"></a>
					    <a href=""><img src="https://raw.githubusercontent.com/gugundwipermana/_icon_share/master/google.png"></a>
					    <a href=""><img src="https://raw.githubusercontent.com/gugundwipermana/_icon_share/master/linkinid.png"></a>
					    <a href="https://youtu.be/n2p2PistcgI"><img src="https://raw.githubusercontent.com/gugundwipermana/_icon_share/master/youtube.png"></a>
					    <a href="https://instagram.com/p/BlUGWDqgqu8/"><img src="https://raw.githubusercontent.com/gugundwipermana/_icon_share/master/instagram.png"></a>
					    <a href="https://play.google.com/apps/internaltest/4697597284297674235"><img src="https://raw.githubusercontent.com/gugundwipermana/_icon_share/master/google_play.png"></a>
					</div>
				</div>
				<div class="column col-3">
					<h3>Alamat</h3>
					<p>Gedung Jaya lantai 6 <br/>JL. MH. Thamrin No. 12, Kelurahan Kebon Sirih, Kecamatan Menteng, Jakarta Pusat, 10340</p>
					<div class="box-img">
						<img src="https://raw.githubusercontent.com/gugundwipermana/_icon_share/master/Handphone.png">
					    <a href="tel:+62213917163"><span>+62 21 391 7163</span></a>
					</div>
					<div class="box-img">
					    <img src="https://raw.githubusercontent.com/gugundwipermana/_icon_share/master/Email.png">
					    <a href="mailto:info@fundtastic.co.id?Subject=Hello"><span>info@fundtastic.co.id</span></a>
					</div>
				</div>
			</div>
		</div>
	</footer>
	<div class="footer">
		<div class="container">
			<hr/>
			<div class="copy-right">
				<p>&copy; PT.Chandharwealth Mandiri Indonesia. Hak Cipta Dilindungi Undang-undang</p>
			</div>
		</div>
	</div>
</body>
</html>