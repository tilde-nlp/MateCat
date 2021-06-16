
let AnalyzeConstants = require('../../constants/AnalyzeConstants');

class JobAnalyzeHeader extends React.Component {

    constructor(props) {
        super(props);
    }

    calculateWords() {
        this.total = 0;
        this.payable = 0;
        let self = this;
        this.props.totals.forEach(function (chunk, i) {
            self.payable = self.payable + chunk.get('TOTAL_PAYABLE').get(0);
        });

        _.each(this.props.jobInfo.chunks, function (chunk, i) {
            self.total = self.total + chunk.total_raw_word_count
        });
    }

    componentDidUpdate() {
    }

    componentDidMount() {
    }

    componentWillUnmount() {
    }

    shouldComponentUpdate(nextProps, nextState){
        return true;
    }

    render() {
        this.calculateWords();
        return <div className="head-chunk sixteen wide column shadow-1 pad-right-10">
                    <div className="source-target">
                        <div className="source-box">{this.props.jobInfo.source}</div>
                        <div className="in-to">
                            <i className="icon-chevron-right icon"/>
                        </div>
                        <div className="target-box">{this.props.jobInfo.target}</div>
                    </div>
                    {/*<div className="job-not-payable">*/}
                        {/*<span id="raw-words">{parseInt(this.total)}</span> Total words*/}
                    {/*</div>*/}
                    <div className="job-payable">
                        <span id="words">{parseInt(this.payable)}</span>
                        { !config.isCJK ? (
                            " MateCat Weighted words"
                        ) : (
                            " MateCat weighted characters"
                        )}

                    </div>

                </div>;

    }
}

export default JobAnalyzeHeader;
