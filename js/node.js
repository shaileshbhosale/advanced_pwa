var webPush = require('web-push');

/*
var pushSubscription = {"endpoint":"https://android.googleapis.com/gcm/send/f1LsxkKphfQ:APA91bFUx7ja4BK4JVrNgVjpg1cs9lGSGI6IMNL4mQ3Xe6mDGxvt_C_gItKYJI9CAx5i_Ss6cmDxdWZoLyhS2RJhkcv7LeE6hkiOsK6oBzbyifvKCdUYU7ADIRBiYNxIVpLIYeZ8kq_A",
"keys":{"p256dh":"BLc4xRzKlKORKWlbdgFaBrrPK3ydWAHo4M0gs0i1oEKgPpWC5cW8OCzVrOQRv-1npXRWk8udnW3oYhIO4475rds=", "auth":"5I2Bu2oKdyy9CwL8QVF0NQ=="}};
*/

process.env.NODE_TLS_REJECT_UNAUTHORIZED = "0";

var pushSubscription = {"endpoint":"https://android.googleapis.com/gcm/send/e1DME_hYCpU:APA91bHZ7GplSjX07qCstTtGMa7JEqGOwYbuPsEFJZHHl4fJZOK5eeWTZqC0-xiNCdQngTdY7cigtdS8bEv-XbRKdHO2RUKOjNbaMgH2lrCNWNmyRs4I5ajVNMSwWJsGbm5SI_DoJRPD","expirationTime":null,"keys":{"p256dh":"BDFzoaZm3TOkzVyHZnrymfgTgixes_hYXTHUkZ-TO-E-lEvrdXaMUP1h_BEA8ysR_2vTbubDXyDGl4dIv0Ml0q4","auth":"Gezy92-wO74xvFmyRFRw_A"}};

var payload = 'Here is a payload!';

var options = {
  gcmAPIKey: 'AIzaSyAQclgRDH-sfnw4DO-TvWWOMwKs0-nftbU',
  TTL: 60
};

webPush.sendNotification(
  pushSubscription,
  payload,
  options
);
