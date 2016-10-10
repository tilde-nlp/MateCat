/**
 * React Component .

 */
var React = require('react');
var SegmentConstants = require('../../constants/SegmentConstants');
var SegmentStore = require('../../stores/SegmentStore');
class SegmentFooterTabMatches extends React.Component {

    constructor(props) {
        super(props);
    }

    componentDidMount() {
        console.log("Mount SegmentFooterMatches" + this.props.id_segment);

    }

    componentWillUnmount() {
        console.log("Unmount SegmentFooterMatches" + this.props.id_segment);

    }

    componentWillMount() {

    }
    allowHTML(string) {
        return { __html: string };
    }

    render() {
        return (
        <div
            key={"container_" + this.props.code}
            className={"tab sub-editor "+ this.props.active_class + " " + this.props.tab_class}
            id={"segment-" + this.props.id_segment + " " + this.props.tab_class}>
            <div className="overflow"></div>
            <div className="engine-errors"></div>
        </div>
        )
    }
}

export default SegmentFooterTabMatches;