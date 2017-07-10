this.state = {
    tag: "div",
    page: ""
}

this.query = (params) => {
    let url = window.yard.url.page.replace('[page]', this.state.page);
    var data = new FormData();
    data.append("json", JSON.stringify(params));

    return fetch(url + "...db", {
        method: "POST",
        body: data
    })
    .then(res => res.text())
    .then(text => {
        console.log(text);
    })
}

this.on('componentWillMount', () => {
    let parent = this._reactInternalInstance._currentElement._owner._currentElement._owner._instance;

    if (!parent.props.name || !parent.props.loader) {
        throw new Error('Parent is not a Page! You should use db:Query inside Yard Page');
    }

    if (this.props.tag) {
        this.setState({
            tag: this.props.tag,
            page: parent.props.name
        });
    }
})

this.on('componentWillUpdate', (nextProps) => {
    this.query(this.props.params);
})