## PhonePe Payment Gateway for Paymenter

![](phonepe.svg)

A simple extension to integrate PhonePe as a payment gateway within your Paymenter platform, making it easy for customers to pay via PhonePe for a smooth, convenient checkout experience.

## Supported Versions
- **Supported Version:** Paymenter v0.9.5
- **Compatibility Note:** This extension is officially supported only on Paymenter v0.9.5. While it may work on other versions, there is no guarantee or support provided for versions other than v0.9.5.

## Installation

- Default Paymenter Webroot: `/var/www/paymenter`
- Gateway Directory: `/var/www/paymenter/app/Extensions/Gateways`

1. Navigate to Gateway Directory and unarchive the zip file
1. Go to Paymenter Admin -> Extensions -> PhonePe -> Edit
1. Enable the extension and setup your PhonePe credentials
1. You are now ready to accept payments using the PhonePe Gateway!

## Configuration

- **Merchant ID:** PhonePe Merchant ID
- **Salt Key:** PhonePe Salt Key
- **Salt Index:** PhonePe Salt Index
- **Order Prefix (optional):** Prefix of the Order ID. Example: Setting prefix as `ORDER-` will make the Order ID for invoice 8 `ORDER-8`
- **Live (checkbox):** Enable this if you want to use the gateway in production

## Demo
You can see the demo of how this integration works by watching this video: [PhonePe Gateway Demo](https://streamable.com/4a3ryt)

## Security Notice

This extension requires sensitive information, such as your PhonePe Merchant ID and Salt Key. To keep your transactions secure:

- Always store these credentials securely and confidentially.

## Support and Purchase

For assistance or to purchase this extension, please reach out to me on [Discord: @vaibhavd](https://discord.com/users/914452175839723550).

> **Note:** Support is not provided for installations or extensions that have been modified. Any alterations to the code are at your own risk, and I am not responsible for any issues or malfunctions caused by such modifications.

## License and Restrictions

- **Redistribution:** This is a commercial extension. You are not permitted to redistribute, resell, or share this extension in any form.
- **Modifications:** Any modifications to the code are not supported, and you assume all responsibility for any issues that arise from changes made to the extension.
- **Trademarks:** The PhonePe name and logo are trademarks of PhonePe Pvt Ltd. This project is not affiliated with or endorsed by PhonePe.

## Changelog

### v1.0.0
- Initial release of PhonePe Payment Gateway integration for Paymenter
- Supports Paymenter  v0.9.5
