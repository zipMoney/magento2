var config = {
    paths: {
            zipMoneyWidgetJs: '//d3k1w8lx8mqizo.cloudfront.net/lib/js/zm-widget-js/dist/zipmoney-widgets-v1.min',
            zipMoneyIframeJs:'https://account.zipmoney.com.au/scripts/iframe/zipmoney-checkout',
            zipMoneyIframeJsSandbox:'https://account.sandbox.zipmoney.com.au/scripts/iframe/zipmoney-checkout',
            zipMoneyCheckoutJs:'https://static.zipmoney.com.au/checkout/checkout-v1.min'
    },
    shim: {
      "zipMoneyWidgetJs": ["jquery"]
    }
};

