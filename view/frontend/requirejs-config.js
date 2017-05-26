var config = {
    paths: {
           // zipMoneyWidgetJs: '//d3k1w8lx8mqizo.cloudfront.net/lib/js/zm-widget-js/dist/zipmoney-widgets-v1.min',
            zipMoneyWidgetJs: '//local.zipmoney.com.au/zipmoney-widget-js/dist/scripts/zipmoney-widgets-v1',
            zipMoneyCheckoutJs:'https://static.zipmoney.com.au/checkout/checkout-v1.min'
    },
    shim: {
      "zipMoneyWidgetJs": ["jquery"]
    }
};

