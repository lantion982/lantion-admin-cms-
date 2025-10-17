
var customer_banks =[
    {"code":"CMBC",    "cn":"民生银行"       },
    {"code":"ICBC",    "cn":"工商银行"       },
    {"code":"ABC",     "cn":"农业银行"       },
    {"code":"CCB",     "cn":"建设银行"       },
    {"code":"CMB",     "cn":"招商银行"       },
    {"code":"BCM",     "cn":"交通银行"       },
    {"code":"CIB",     "cn":"兴业银行"       },
    {"code":"CNCB",    "cn":"中信银行"       },
    {"code":"CEB",     "cn":"光大银行"       },
    {"code":"BOC",     "cn":"中国银行"       },
    {"code":"HXB",     "cn":"华夏银行"       },
    {"code":"PAB",     "cn":"平安银行"       },
    {"code":"SPDB",    "cn":"浦发银行"       },
    {"code":"PSBC",    "cn":"中国邮政"       },
    {"code":"GDB",     "cn":"广发银行"       },
    {"code":"ALIPAY",  "cn":"支付宝"         },
    {"code":"WebMM",   "cn":"微信支付"       }
];


function get_bank_name(bcode)
{
    var map = {
        "ICBC":   "工商银行",
        "ABC":    "农业银行",
        "CCB":    "建设银行",
        "SPDB":   "浦发银行",
        "CIB":    "兴业银行",
        "CMBC":   "民生银行",
        "BCM":    "交通银行",
        "COMM":   "交通银行",
        "CNCB":   "中信银行",
        "CEB":    "光大银行",
        "BCCB":   "北京银行",
        "CMB":    "招商银行",
        "GDB":    "广发银行",
        "SHB":    "上海银行",
        "BOC":    "中国银行",
        "HXB":    "华夏银行",
        "PAB":    "平安银行",
        "PSBC":   "中国邮政",
        "SDB":    "深圳发展银行",
        "RCC":    "农村信用社",
        "HSB":    "徽商银行",
        "ALIPAY":     "支付宝",
        "WebMM":      "微信支付",
        "QQPAY":      "QQ钱包",
        "TENPAY":     "财付通",
        "BESTPAY":    "翼支付",
        "JDPAY":      "京东钱包",
        "BIDUPAY":    "百度钱包",
        "UNIPAY":     "银联钱包",
        "UNICARD":    "银联卡",
        "BANKPAY":    "银行转账",
        "CREDITCARD": "信用卡"
    };

    if ( map[bcode] )
        return map[bcode];
    else
        return bcode;
}

var isMobile = {
    Android: function() {
        return navigator.userAgent.match(/Android/i);
    },
    BlackBerry: function() {
        return navigator.userAgent.match(/BlackBerry/i);
    },
    iOS: function() {
        return navigator.userAgent.match(/iPhone|iPad|iPod/i);
    },
    Opera: function() {
        return navigator.userAgent.match(/Opera Mini/i);
    },
    Windows: function() {
        return navigator.userAgent.match(/IEMobile/i);
    },
    any: function() {
        return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows());
    }
};

function show_dialog (divid) {
    var w = ($(window).width() - $(''+divid).width()) / 2;
    var h = ($(window).height() - $(''+divid).height()) / 2 - 50;
    if (h < 10) h = 10;
    h = h + $(document).scrollTop();
    $(''+divid).css({"display":"block", "position":"absolute", "top":h+"px", "left":w+"px"});
}

function jpost(u, d, cb) {
    $.ajax({
        type: "POST",
        url: u,
        data: JSON.stringify(d),
        dataType: "json",
        contentType : 'application/json',
        success: function (data) {
            if (cb) cb(data);
        }
	});
}

function format_date(ddd)
{
    function pad(n){return (n<10 ? '0'+n : n);}
    var d = new Date(ddd);
    return d.getFullYear()+'-'
        + pad(d.getMonth()+1)+'-'
        + pad(d.getDate())+' '
        + pad(d.getHours())+':'
        + pad(d.getMinutes())+':'
        + pad(d.getSeconds());
}

