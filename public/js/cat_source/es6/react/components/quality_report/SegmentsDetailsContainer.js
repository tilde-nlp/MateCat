import Filters from "./FilterSegments";
import FileDetails from "./FileDetails"
import QualityReportActions from "./../../actions/QualityReportActions"

class SegmentsDetails extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            filter: null
        };
    }
    getFiles() {
        let files = [];
        if ( this.props.files ) {
            this.props.files.keySeq().forEach(( key, index ) => {
                let file = <FileDetails key={key} file={this.props.files.get(key)} urls={this.props.urls}/>
                files.push(file)
                this.lastSegment = this.props.files.get(key).get('segments').last().get('sid');
            });
        }
        return files;
    }

    scrollDebounceFn() {
        let self = this;
        return _.debounce(function() {
            self.onScroll();
        }, 200)
    }

    onScroll(){
        if ( $(window).scrollTop() + $(window).height() > $(document).height() - 200)  {
            console.log("Load More Segments!");
            if (this.props.moreSegments) {
                QualityReportActions.getMoreQRSegments(this.state.filter, this.lastSegment);
            }
        }
    }
    filterSegments(filter) {
        this.setState({
            filter: filter
        });
        this.lastSegment = 0;
        QualityReportActions.filterSegments(filter, null)
    }

    componentDidMount() {
        window.addEventListener('scroll', this.scrollDebounceFn(), false);
    }

    componentWillUnmount() {
        window.removeEventListener('scroll', this.scrollDebounceFn(), false);
    }

    render () {

        return <div className="qr-segment-details-container">
            <div className="qr-segments-summary">
                <div className="qr-filter-container">
                    <h3>Segment details</h3>
                    <Filters applyFilter={this.filterSegments.bind(this)}
                            categories={this.props.categories}
                    />
                </div>
                {this.props.files && this.props.files.size === 0 ?
                    <div className="no-segments-found" >No segments found</div>

                 : (
                    this.getFiles()

                ) }

                {this.props.moreSegments && this.props.files && this.props.files.size !== 0 ? (
                    <div className="ui one column grid">
                        <div className="one column spinner" style={{height: "100px"}}>
                            <div className="ui active inverted dimmer">
                                <div className="ui medium text loader">Loading more segments</div>
                            </div>
                        </div>
                    </div>
                ):null}

            </div>
        </div>
    }
}

export default SegmentsDetails ;