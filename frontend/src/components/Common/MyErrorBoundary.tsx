import { Component, ErrorInfo, ReactNode } from "react";
import { Typography } from "@mui/material";

interface Props {
  children?: ReactNode;
}

interface State {
  errorMessage?: string;
}

export default class MyErrorBoundary extends Component<Props, State> {
  public state: State = {
    errorMessage: undefined,
  };

  setErrorMessage = (message: string) => {
    this.setState({
      errorMessage: message,
    });
  };

  public static getDerivedStateFromError(error: Error): State {
    return { errorMessage: error.message };
  }

  public componentDidCatch(error: Error, errorInfo: ErrorInfo) {
    console.error("Uncaught error:", error, errorInfo);
    this.setErrorMessage(error.message);
  }

  render() {
    const { errorMessage } = this.state;
    const { children } = this.props;

    return errorMessage != null ? (
      <Typography>{errorMessage}</Typography>
    ) : (
      children
    );
  }
}
