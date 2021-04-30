var rand1 = 0;  
var useRand = 0;  
images = new Array;
images[1] = new Image();  
images[1].src = "ui/img/avatar(1).png";
images[2] = new Image();  
images[2].src = "ui/img/avatar(2).png";
images[3] = new Image();  
images[3].src = "ui/img/avatar(3).png";
images[4] = new Image();  
images[4].src = "ui/img/avatar(4).png";
images[5] = new Image();  
images[5].src = "ui/img/avatar(5).png";
images[6] = new Image();  
images[6].src = "ui/img/avatar(6).png";
images[7] = new Image();  
images[7].src = "ui/img/avatar(7).png";
images[8] = new Image();
images[8].src = "ui/img/avatar(8).png";
images[9] = new Image();
images[9].src = "ui/img/avatar(9).png";
images[10] = new Image();
images[10].src = "ui/img/avatar(10).png";
images[11] = new Image();
images[11].src = "ui/img/avatar(11).png";
images[12] = new Image();
images[12].src = "ui/img/avatar(12).png";
images[13] = new Image();
images[13].src = "ui/img/avatar(13).png";
images[14] = new Image();
images[14].src = "ui/img/avatar(14).png";
images[15] = new Image();
images[15].src = "ui/img/avatar(15).png";
images[16] = new Image();
images[16].src = "ui/img/avatar(16).png";

function change() {
var imgnum = images.length - 1;  
do {  
var randnum = Math.random();  
rand1 = Math.round((imgnum - 1) * randnum) + 1;
} while (rand1 == useRand);
useRand = rand1;  
document.randimg.src = images[useRand].src;

}  