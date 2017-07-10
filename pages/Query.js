let requestLatest = latest(request);

this.state = {
    tag: "div",
    result: null,
    requery: false,
    loading: false,
    debug: false
}
this.page = false;
this.promise = false;

this.query = (params) => {
    let url = window.yard.url.page.replace('[page]', this.page);

    if (typeof params == "undefined") {
        params = this.props.params;
    }

    this.setState({
        loading: true
    });

    let spec = this.props.spec.split(":");
    if (spec.length > 1) {
        spec = spec[1];
    } else {
        spec = '';
    }
    this.promise = requestLatest(
        {
            url: url + "...db_" + spec,
            method: "POST",
            headers: {
                "Content-type": "application/json"
            },
            body: JSON.stringify(params)
        })
        .then(res => {
            let result = res;

            try {
                result = JSON.parse(res)
            } catch (error) {}

            this.setState({
                requery: false,
                loading: false,
                result
            });
        });

    return this.promise;
}

this.on('componentWillMount', () => {
    let parent = this._reactInternalInstance._currentElement._owner._currentElement._owner._instance;

    if (!parent.props.name || !parent.props.loader) {
        throw new Error('Parent is not a Page! You should use db:Query inside Yard Page');
    }
    this.page = parent.props.name;

    if (this.props.bind) {
        this.props.bind(this);
    }

    if (this.props.tag) {
        this.setState({
            tag: this.props.tag,
        });
    }
})

this.on('componentWillUpdate', (nextProps) => {
    if (this.state.debug != nextProps.debug) {
        this.setState({ debug: nextProps.debug });
    }
})

this.on('componentDidUpdate', () => {
    let isInit = this.state.result === null && !this.promise;
    let shouldQuery = this.state.requery && !this.state.loading;

    if (isInit || shouldQuery) {
        this.query(this.props.params);
    }
})