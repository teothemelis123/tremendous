import FormHandler from './form-handler';

export default class Tremendous {
  static init() {
    const data = ['rewards_selection'];
    const TremendousFormHandler = new FormHandler('.tremendous_request-form', 'tremendous_request', data);
    TremendousFormHandler.registerSubmit((form, response) => {
      if (typeof (response.response) === 'undefined') {
        TremendousFormHandler.displayMessage(form, response, 'danger');
      } else if (response.response.code === 200) {
        TremendousFormHandler.displayMessage(form, 'Your order was successful please check your mail to choose your reward type.', 'success');
      } else {
        TremendousFormHandler.displayMessage(form, response.response.message, 'danger');
      }
    });
  }
}
