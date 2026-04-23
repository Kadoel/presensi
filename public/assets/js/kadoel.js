//Mengubah Format Detik Ke dalam format Waktu "Jam:Menit:Detik"
String.prototype.toHHMMSS = function (param) {
  var sec_num = parseInt(this, 10); // don't forget the second param
  var hours = Math.floor(sec_num / 3600);
  var minutes = Math.floor((sec_num - hours * 3600) / 60);
  var seconds = sec_num - hours * 3600 - minutes * 60;

  if (hours < 10) {
    hours = "0" + hours;
  }
  if (minutes < 10) {
    minutes = "0" + minutes;
  }
  if (seconds < 10) {
    seconds = "0" + seconds;
  }
  if (param == "H:i") {
    return hours + ":" + minutes;
  }
  return hours + ":" + minutes + ":" + seconds;
};

//Mengubah Format Waktu "Jam:Menit" ke dalam format detik
String.prototype.toSeconds = function () {
  var a = this.split(":"); //Pisahkan Berdasarkan :
  let seconds;
  if (a.length == 2) {
    seconds = +a[0] * 60 * 60 + +a[1] * 60;
  } else {
    seconds = +a[0] * 60 * 60 + +a[1] * 60 + +a[2];
  }
  return seconds;
};

String.prototype.toHariText = function () {
  let hari = this;
  let hari_text = "";
  if(hari == 1){
    hari_text = "Senin";
  }
  else if(hari == 2){
    hari_text = "Selasa";
  }
  else if(hari == 3){
    hari_text = "Rabu";
  }
  else if(hari == 4){
    hari_text = "Kamis";
  }
  else if(hari == 5){
    hari_text = "Jumat";
  }else{
    hari_text = "Sabtu";
  }

  return hari_text;
};

//Mengkonversi format tanggal "1992-10-03" ke "10 Januari 1992"
String.prototype.toTanggalIndonesia = function () {
  let splitStr = this.split("-");
  let tanggal = splitStr[2];
  let bulan = parseInt(splitStr[1]);
  let tahun = splitStr[0];

  const bulanArray = [
    "Free",
    "Januari",
    "Februari",
    "Maret",
    "April",
    "Mei",
    "Juni",
    "Juli",
    "Agustus",
    "September",
    "Oktober",
    "November",
    "Desember",
  ];
  return tanggal + " " + bulanArray[bulan] + " " + tahun;
};

//mengekstract Data Array [{"key: value"}, {"key: value"}] menjadi array [value]
Array.prototype.ekstrakDataArray = function () {
  let dataArray = [];
  this.forEach((data) => {
    dataArray.push(data.id, data.tanggal);
  });
  return dataArray;
};

//Cek Data Ada Di Dalam Array => true / false
Array.prototype.checkArrayExist = function (param) {
  let sources = this.ekstrakDataArray();
  return sources.includes(param);
};

String.prototype.toCapitalizeEachWord = function(){
  let splitStr = this.toLowerCase().split(' ');
  for (let i = 0; i < splitStr.length; i++) {
    splitStr[i] = splitStr[i].charAt(0).toUpperCase() + splitStr[i].substring(1);     
  }
  return splitStr.join(' '); 
};