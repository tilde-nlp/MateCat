let ReviewVersionDiff =  require("./ReviewVersionsDiff").default;
class ReviewExtendedIssue extends React.Component {

	constructor(props) {
		super(props);
		this.state = {
			extendDiffView: false,
            sendDisabled : true,
            rebutDisabled: true
		};

	}

	categoryLabel() {
		let id_category = this.props.issue.id_category;
		config.lqa_flat_categories = config.lqa_flat_categories.replace(/\"\[/g, "[")
			.replace(/\]"/g, "]")
			.replace(/\"\{/g, "{")
			.replace(/\}"/g, "}");
		return _(JSON.parse(config.lqa_flat_categories))
			.select(function (e) {
				return parseInt(e.id) == id_category;
			}).first().label
	}

	deleteIssue(event) {
		event.preventDefault();
		event.stopPropagation();
		SegmentActions.deleteIssue(this.props.issue)
	}
	setExtendedDiffView(event){
		event.preventDefault();
		event.stopPropagation();
		this.setState({extendDiffView : !this.state.extendDiffView})
	}


    handleCommentChange(event) {
        var text = event.target.value,
        	disabled = true;

        if ( text.length > 0 ) {
            disabled = false;
        }
        this.setState({
            comment_text : text,
            sendDisabled : disabled,
        });
    }

	addComment(){

        // send action invokes ReviewImproved function
        if ( !this.state.comment_text || this.state.comment_text.length === 0 ) {
            return ;
        }

        var data = {
            rebutted : true,
            message : this.state.comment_text,
            source_page : (config.isReview ? 2 : 1)  // TODO: move this to UI property
        };

        this.setState({sendDisabled : true});

        SegmentActions
            .submitComment( this.props.sid, this.props.issueId, data )
            .fail( this.handleFail );
	}

    handleFail() {
        genericErrorAlertMessage() ;
        this.setState({ sendDisabled : false });
    }

	render() {
		let category_label = this.categoryLabel();
		let formatted_date = moment(this.props.issue.created_at).format('lll');

		let commentLine = null;
		if (this.props.issue.comment) {
			commentLine = <div className="review-issue-thread-entry">
				<strong>Comment:</strong> {comment}</div>;
		}
		let extendedViewButtonClass = (this.state.extendDiffView ? "re-active" : "");

		return <div className="issue-item">
			<div className="issue">
				<div className="issue-head">
					<p><b>{category_label}</b>: {this.props.issue.severity}</p>
				</div>
				<div className="issue-activity-icon">
					<div className="icon-buttons">
						<button className={extendedViewButtonClass} onClick={this.setExtendedDiffView.bind(this)}><i className="icon-eye icon"/></button>
						<button><i className="icon-uniE96E icon"/></button>
						{this.props.isReview ? (<button onClick={this.deleteIssue.bind(this)}><i className="icon-trash-o icon"/></button>): (null)}
					</div>
				</div>
				{this.props.issue.target_text ?
					(<div className="selected-text">
						<p><b>Selected text</b>: <span className="selected">{this.props.issue.target_text}</span></p>
					</div>):(null)}

			</div>
			<div className="re-add-comment">
				<form className="ui form">
					<div className="field">
						<input className="re-comment-input" type="text" name="first-name" placeholder="Add a comment + press Enter" />
					</div>
				</form>
			</div>
			<div className="re-comment-list">
				<div className="re-comment"><span className="re-revisor">Revisor: </span>Questo è un commento del revisore</div>
				<div className="re-comment"><span className="re-translator">Translator </span>Questa è una risposta del traduttore</div>
			</div>
			{this.state.extendDiffView ?
				<ReviewVersionDiff
					diffPatch={this.props.issue.diff}
					segment={this.props.segment}
					decodeTextFn={UI.decodeText}
					selectable={false}
				/> : null}

			<div className="issue-date">
				<i>({formatted_date})</i>
			</div>
		</div>
	}
}

export default ReviewExtendedIssue;