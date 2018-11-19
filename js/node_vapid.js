var webpush = require('web-push');

// VAPID keys should only be generated only once.
//const vapidKeys = webpush.generateVAPIDKeys();

var vapidPublicKey = 'BKG3F5Z_8fH5_iYPQyYwFKWgVBe2SGrYdsVpdPbZ856F227yZKCNrJAMm3b8NOY3NefbC3_sTxJa2U5D8rsUX9E';
var vapidPrivateKey = 'DaQyQpr4_QZxKmxGwo_1Mhmc6W06YykKeMZ3z_EN3MM';

webpush.setGCMAPIKey('AIzaSyAQclgRDH-sfnw4DO-TvWWOMwKs0-nftbU');
webpush.setVapidDetails(
  'mailto:example@yourdomain.org',
  vapidPublicKey,
  vapidPrivateKey
);

process.env.NODE_TLS_REJECT_UNAUTHORIZED = "0";

var pushSubscription = {"endpoint":"https://fcm.googleapis.com/fcm/send/frpMLM9rb8o:APA91bET81uUF1xOdL9mye_qHAmrr9eOS4LExWk0vUB_BACGYpoczCMx0SxKhlCOYkCKwRPMp0sQYZx7OefWEF-YcbDoqBTbGskAblXjO03uLLCjVxF6pmA-6WKs1wfbnfvz8AujDrqb","expirationTime":null,"keys":{"p256dh":"BHEP8uCydnYYbblo-ZH_lawL3eXqEVqW-OYqwJMBHEaEtD8ZWsiEPFCVKEEzf8A9G_2y19PLVhr4UyodUbqvayY","auth":"w11-aWjd55eNlXd2_V8aiw"}};

var payload = 'Push Payload String';

const options = {
  gcmAPIKey: 'AIzaSyAQclgRDH-sfnw4DO-TvWWOMwKs0-nftbU',
  vapidDetails: {
    subject: 'mailto:mandarmbhagwat@gmail.com',
    publicKey: vapidPublicKey,
    privateKey: vapidPrivateKey
  },
  TTL: 60
}

webpush.sendNotification(
  pushSubscription,
  payload,
  options
);

