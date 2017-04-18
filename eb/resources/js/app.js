var __extends = (this && this.__extends) || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
};
var AppClass = (function (_super) {
    __extends(AppClass, _super);
    function AppClass(properties, jq) {
        if (properties === void 0) { properties = {}; }
        _super.call(this, properties, jq);
        this._propertiesDefault = {
            controllers: {
                auth: ''
            },
            errorsHandlers: {
                401: function () {
                    window.location.href = "/" + App.props('controllers').auth;
                },
                403: function () {
                    window.location.href = "/" + App.props('controllers').denied;
                }
            },
            onInit: [],
            onRequestComplete: [],
            onRequestError: [],
            onResize: [],
            onResponseSuccess: [],
            onResponseError: []
        };
        this.props(this._mergeProperties(this._propertiesDefault, properties));
        this.init(properties);
    }
    AppClass.prototype.init = function (properties) {
        var app = this;
        $(window).on('resize', function (event) {
            app.resize(event);
        });
        this.on('requestError', function (sender, xhr, params) {
            if (params === void 0) { params = {}; }
            app.requestError(xhr);
        });
        this.on('responseSuccess', function (sender, xhr, params) {
            if (params === void 0) { params = {}; }
            app.changeContent(params);
        });
        this.on('resize', function (sender, xhr, params) {
            if (params === void 0) { params = {}; }
            app.jq.find('.top-fix').height(app.jq.find('.top').height());
        });
        _super.prototype.init.call(this, properties);
    };
    AppClass.prototype.requestError = function (xhr) {
        if (this.properties.errorsHandlers[xhr.status] !== undefined) {
            this.properties.errorsHandlers[xhr.status]();
        }
    };
    AppClass.prototype.resize = function (event) {
        this.trigger('resize', event, {});
    };
    return AppClass;
}(ObjectClass));
//# sourceMappingURL=app.js.map