# GMT MailChimp
Create barebones MailChimp forms that let you add people to lists and interest groups within those lists (optional). GMT MailChimp forms *only* include an email field for maximum conversion.

[Download GMT MailChimp](https://github.com/cferdinandi/gmt-mailchimp/archive/master.zip)



## Getting Started

Getting started with GMT MailChimp is as simple as installing a plugin:

1. Upload the `gmt-mailchimp` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the Plugins menu in WordPress.
3. Under `MailChimp` > `Options` in the WordPress Dashboard, add your [MailChimp API key](https://developer.mailchimp.com/).

And that's it, you're done. Nice work!

It's recommended that you also install the [GitHub Updater plugin](https://github.com/afragen/github-updater) to get automatic updates.



## Advanced Options

Under `MailChimp` > `Options` in the WordPress Dashboard, you can add:

- A default [List ID](http://kb.mailchimp.com/lists/managing-subscribers/find-your-list-id) to use for all forms you create
- A class to apply to the email field label (useful if you want to add a screen reader class to visually hide the label).
- A class to add to the submit button.
- A class to create a hidden honeypot field for added spam prevention.



## Creating MailChimp forms

Create new MailChimp forms under "MailChimp" in the WordPress Dashboard.

- The `List ID` will default to whatever you provided under the `Options` menu, if anything. This field is mandatory and must be filled out.
- `Category` provides a list of Interest Groups from your specified MailChimp list. [optional]
- `Group` indicates which specific group within a category to add the user to. This menu is blank until a Category is selected. [optional]
- You can also customize all four alert messages.

***Note:*** *The `Category` menu will not populate unless a `List ID` is provided and you click `Publish` or `Update`. Similarly, the `Group` menu will not populate until a `Category` is selected and you click `Update`.*

Use the shortcode provided to add a form to yoru site in the Content Editor. You can add an email field placeholder with the `placeholder` attribute:

```html
[mailchimp id="7832" label="Subscribe" placeholder="Your email address..."]
```



## How to Contribute

Please read the [Contribution Guidelines](CONTRIBUTING.md).



## License

The code is available under the [MIT License](LICENSE.md).