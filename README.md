## PhonePe Payment Gateway for Paymenter

![](phonepe.svg)

A simple extension to integrate PhonePe as a payment gateway within your Paymenter platform, making it easy for customers to pay via PhonePe for a smooth, convenient checkout experience.

## Supported Versions
- **Supported Version:** Paymenter v0.9.5
- **Compatibility Note:** This extension is officially supported only on Paymenter v0.9.5. While it may work on other versions, there is no guarantee or support provided for versions other than v0.9.5.

## Setup

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

## Support

For assistance, please reach out to me on [Discord: @vaibhavd](https://discord.com/users/914452175839723550).

## License

- **License:** This is a opensource extension licensed under the [MIT License](LICENSE). The extension is opensource on GitHub: [PhonePe Paymenter GitHub](https://github.com/VaibhavSys/PhonePe-Paymenter)
- **Trademarks:** The PhonePe name and logo are trademarks of PhonePe Pvt Ltd. This project is not affiliated with or endorsed by PhonePe.
