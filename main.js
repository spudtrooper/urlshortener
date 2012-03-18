function sendMail(to,subject,msg) {
  if (!to || to == '') to = 'RECIEPIENT';
  document.location = 'mailto:' + to + '?body=' + msg + '&subject=' + subject;
}

function sendIM(msg) {
  var username = prompt('To whom are you sending?');
  if (username) {
    document.location = 'aim:goim?screenname=' 
      + escape(username) + '&message=' + escape(msg);
  }
}

function sendYahoo(msg) {
  var username = prompt('To whom are you sending?');
  if (username) {
    document.location = 'ymsgr:sendIM?' 
      + escape(username) + '&m=' + escape(msg);
  }
}

function sendSkype(msg) {
  var username = prompt('To whom are you sending?');
  if (username) {
    document.location = 'aim:goim?screenname=' 
      + escape(username) + '&message=' + escape(msg);
  }
}

function sendTwitter(msg) {
	document.location = 'http://twitter.com/?status=' + escape(msg);
}