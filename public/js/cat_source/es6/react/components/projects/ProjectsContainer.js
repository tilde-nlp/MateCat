// var React = require('react');
let ProjectContainer = require('./ProjectContainer').default;
let FilterProjects = require("../header/manage/FilterProjectsStatus").default;


class ProjectsContainer extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            projects : [],
            more_projects: true,
            reloading_projects: false,
            team: null,
            teams: null,
            filtering: false
        };
        this.renderProjects = this.renderProjects.bind(this);
        this.updateProjects = this.updateProjects.bind(this);
        this.updateTeam = this.updateTeam.bind(this);
        this.updateTeams = this.updateTeams.bind(this);
        this.hideSpinner = this.hideSpinner.bind(this);
        this.showProjectsReloadSpinner = this.showProjectsReloadSpinner.bind(this);
    }


    renderProjects(projects, team, hideSpinner, filtering) {
        let more_projects = true;
        if (hideSpinner) {
            more_projects = this.state.more_projects
        }
        let teamState = (team)? team : this.state.team;
        let filteringState = (filtering) ? filtering : this.state.filtering
        this.setState({
            projects: projects,
            more_projects: more_projects,
            reloading_projects: false,
            team: teamState,
            filtering: filteringState
        });
    }

    updateTeam(team) {
        if (team.get('id') === this.state.team.get('id')) {
            this.setState({
                team: team,
            });
        }
    }

    updateTeams(teams) {
        this.setState({
            teams: teams,
        });
    }

    updateProjects(projects) {
        this.setState({
            projects: projects,
        });
    }

    hideSpinner() {
        this.setState({
            more_projects: false
        });
    }

    showProjectsReloadSpinner() {
        this.setState({
            reloading_projects: true
        });
    }


    openAddMember () {
        ManageActions.openModifyTeamModal(this.state.team.toJS());
    }

    createNewProject() {
        window.open("/", '_blank');
    }

    getButtonsNoProjects() {
        if (!this.state.team) return;

        let thereAreMembers = (this.state.team.get("members") && this.state.team.get("members").size > 1 ||
            this.state.team.get("pending_invitations") && this.state.team.get("pending_invitations").size > 0) || this.state.team.get('type') === 'personal';
        return <div className="notify-notfound">
            {(this.state.filtering )? (
                <div>
                    <div className="message-nofound">No Projects Found</div>
                    <div className="no-results-found"></div>
                </div>
                ) :(this.state.team.get('type') === 'personal' ?
                        (<div className="no-results-teams">
                                <div className="message-nofound">Welcome to your Personal area</div>
                                <div className="welcome-to-matecat">

                                </div>
                                <div className="message-create">{/*Lorem ipsum dolor sit amet*/}
                                    <p>
                                        <a className="ui primary button" onClick={this.createNewProject.bind(this)}>
                                            Create Project
                                        </a>
                                    </p>
                                    {!thereAreMembers ? (
                                        <p>
                                            <a className="ui primary button" onClick={this.openAddMember.bind(this)}>
                                                Add member
                                            </a>
                                        </p>
                                    ) : ('')}
                                </div>
                            </div>
                        ):(
                        <div className="no-results-teams">
                            <div className="message-nofound">Welcome to  {this.state.team.get('name')}</div>
                            <div className="no-results-found">

                            </div>
                            <div className="message-create">{/*Lorem ipsum dolor sit amet*/}
                                <p>
                                    <a className="ui primary button" onClick={this.createNewProject.bind(this)}>
                                        Create Project
                                    </a>
                                {!thereAreMembers ? (

                                    <a className="ui primary button" onClick={this.openAddMember.bind(this)}>
                                        Add member
                                    </a>
                                ) : ('')}
                                </p>
                            </div>
                        </div>
                    )
                )}

        </div>;
    }

    componentDidMount() {
        ProjectsStore.addListener(ManageConstants.RENDER_PROJECTS, this.renderProjects);
        // ProjectsStore.addListener(ManageConstants.RENDER_ALL_TEAM_PROJECTS, this.renderAllTeamssProjects);
        ProjectsStore.addListener(ManageConstants.UPDATE_PROJECTS, this.updateProjects);
        ProjectsStore.addListener(ManageConstants.NO_MORE_PROJECTS, this.hideSpinner);
        ProjectsStore.addListener(ManageConstants.SHOW_RELOAD_SPINNER, this.showProjectsReloadSpinner);
        TeamsStore.addListener(TeamConstants.UPDATE_TEAM, this.updateTeam);
        TeamsStore.addListener(TeamConstants.UPDATE_TEAMS, this.updateTeams);
        TeamsStore.addListener(TeamConstants.RENDER_TEAMS, this.updateTeams);
    }

    componentWillUnmount() {
        ProjectsStore.removeListener(ManageConstants.RENDER_PROJECTS, this.renderProjects);
        // ProjectsStore.removeListener(ManageConstants.RENDER_ALL_TEAM_PROJECTS, this.renderAllTeamssProjects);
        ProjectsStore.removeListener(ManageConstants.UPDATE_PROJECTS, this.updateProjects);
        ProjectsStore.removeListener(ManageConstants.NO_MORE_PROJECTS, this.hideSpinner);
        ProjectsStore.removeListener(ManageConstants.SHOW_RELOAD_SPINNER, this.showProjectsReloadSpinner);
        TeamsStore.removeListener(TeamConstants.UPDATE_TEAM, this.updateTeam);
        TeamsStore.removeListener(TeamConstants.UPDATE_TEAMS, this.updateTeams);
        TeamsStore.removeListener(TeamConstants.RENDER_TEAMS, this.updateTeams);
    }

    componentDidUpdate() {
        let self = this;
        if (!this.state.more_projects) {
            setTimeout(function () {
                $(self.spinner).css("visibility", "hidden");
            }, 3000);
        }
    }

    componentWillUpdate() {}


    shouldComponentUpdate(nextProps, nextState) {
        return (!nextState.projects.equals(this.state.projects) ||
        nextState.more_projects !== this.state.more_projects ||
        nextState.reloading_projects !== this.state.reloading_projects ||
        !nextState.team.equals(this.state.team) ||
        !nextState.teams.equals(this.state.teams) )
    }

    render() {
        let self = this;
        let projects = this.state.projects;

        let items = projects.map((project, i) => (
            <ProjectContainer
                key={project.get('id')}
                project={project}
                lastActivityFn={this.props.getLastActivity}
                changeJobPasswordFn={this.props.changeJobPasswordFn}
                downloadTranslationFn={this.props.downloadTranslationFn}
                team={this.state.team}
                teams={this.state.teams}/>
        ));

        let spinner = '';
        if (this.state.more_projects && projects.size > 9) {
            spinner = <div className="ui one column shadow-1 grid">
                <div className="one column spinner" style={{height: "100px"}}>
                    <div className="ui active inverted dimmer">
                        <div className="ui medium text loader">Loading more projects</div>
                    </div>
                </div>
            </div>;
        } else if (projects.size > 9) {
            spinner = <div className="ui one column shadow-1 grid" ref={(spinner) => this.spinner = spinner}>
                <div className="one column spinner center aligned">
                        <div className="ui medium header">No more projects</div>
                </div>
            </div>;
        }

        if (!items.size) {
            items = this.getButtonsNoProjects();
            spinner = '';
        }

        var spinnerReloadProjects = '';
        if (this.state.reloading_projects) {
            var spinnerContainer = {
                position: 'absolute',
                height : '100%',
                width : '100%',
                backgroundColor: 'rgba(76, 69, 69, 0.3)',
                top: $(window).scrollTop(),
                left: 0,
                zIndex: 3
            };
            spinnerReloadProjects =<div style={spinnerContainer}>
                    <div className="ui active inverted dimmer">
                        <div className="ui massive text loader">Updating Projects</div>
                    </div>
                </div>;
        }



        return <div>
                    <div className="project-list">
                        <div className="ui container">
                            {spinnerReloadProjects}
                            {items}
                            {spinner}
                        </div>

                    </div>
                </div>;
    }
}


export default ProjectsContainer ;